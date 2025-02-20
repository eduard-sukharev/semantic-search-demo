<?php

namespace App\Command;

use App\Entity\NewsPost;
use App\Service\CsvReader;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load_csv',
    description: 'Load CSV files from migrations folder',
)]
class LoadCsvCommand extends Command
{
    private LocalFilesystemAdapter $adapter2014;
    private LocalFilesystemAdapter $adapter2015;
    private \PDO $pdo;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
//        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $dsn = "pgsql:host=postgres;dbname=project;port=5432;client_encoding=utf8";

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->pdo = new \PDO($dsn, 'postgres', 'postgres', $options);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Loading files');

        $filesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__.'/../../migrations/'));

        $filepaths = $filesystem->listContents('/2015')
            ->filter(fn(StorageAttributes $attributes) => $attributes->isFile())
            ->map(fn (StorageAttributes $attributes) => $attributes->path())
            ->toArray();
        $rows = [];
        foreach ($filepaths as $filepath) {
            $io->note($filepath);

            $csvReader = new CsvReader(__DIR__.'/../../migrations/' . $filepath);
            $chunkSize = 0;
            foreach ($csvReader->getRows() as $row) {
                $chunkSize++;
//                $this->entityManager->getConnection()->insert(
//                    'news_post',
//                    [
//                        'title' => $row['title'],
//                        'text' => $row['text'],
//                        'date' => Carbon::parse($row['date'])->format(Carbon::ATOM),
//                    ]
//                );
                $rows[] = [
                    'title' => $row['title'],
                    'text' => $row['text'],
                    'date' => Carbon::parse($row['date'])->format(Carbon::ATOM),
                ];

                if ($chunkSize >= 1000) {
                    $chunkSize = 0;
//                    $this->entityManager->flush();
                    $this->saveToDb($rows);
                    $rows = [];
                    $io->write('#');
                }
            }
            $this->saveToDb($rows);
//            $this->entityManager->flush();
//            $this->entityManager->clear();
        }

        $io->success('Loading done');

        return Command::SUCCESS;
    }

    private function saveToDb($rows)
    {
        $stmt = $this->pdo->prepare("INSERT INTO news_post (title, text, date) VALUES (:title, :text, :date)");
        try {
            $this->pdo->beginTransaction();
            foreach ($rows as $row)
            {
                $stmt->execute($row);
            }
            $this->pdo->commit();
        }catch (\Throwable $e){
            $this->pdo->rollback();
            throw $e;
        }
    }
}
