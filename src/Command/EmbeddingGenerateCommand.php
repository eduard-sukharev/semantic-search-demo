<?php

namespace App\Command;

use App\Entity\ModelEnum;
use App\Repository\NewsPostVectorStore;
use Doctrine\ORM\EntityManagerInterface;
use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\OllamaConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:embedding:generate',
    description: 'Get a batch of news posts and generates embeddings data for them',
)]
class EmbeddingGenerateCommand extends Command
{
    public function __construct(
        private readonly NewsPostVectorStore $store,
        private readonly EntityManagerInterface $em,
        private readonly string $ollamaUrl,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Model alias', suggestedValues: ModelEnum::cases())
            ->addOption('batch', 'b', InputOption::VALUE_REQUIRED, 'Size of batch to process')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit of posts to process in batches')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $model = ModelEnum::from($input->getOption('model'));
        $config = new OllamaConfig();
        $config->url = $this->ollamaUrl;
        $config->model = $model->modelName();
        $embeddingGenerator = new OllamaEmbeddingGenerator($config);

        $io = new SymfonyStyle($input, $output);

        $limit = 1;
        if ($input->getOption('limit')) {
            $limit = $input->getOption('limit');
        }
        $batch = $limit;
        if ($input->getOption('batch')) {
            $batch = $input->getOption('batch');
        }

        $processed = 0;
        while ($processed < $limit) {
            try {
                $this->em->beginTransaction();
                $documents = $this->store->fetchDocumentsWithoutEmbedding($model, limit: $limit % $batch ?: $batch);
                foreach ($documents as $document) {
                    $embedding = $embeddingGenerator->embedText(
                        $document->title . " " . $document->content
                    );
                    switch ($model) {
                        case ModelEnum::ENBEDDRUS:
                            $document->embeddingEnbeddrus = $embedding;
                            break;
                        case ModelEnum::ARCTIC2:
                            $document->embeddingArctic2 = $embedding;
                            break;
                        case ModelEnum::BGE2:
                            $document->embeddingBgeM3 = $embedding;
                            break;
                    }
                    $processed++;
                    $this->store->addDocument($document);
                }
                $io->write('.');
                $this->em->flush();
                $this->em->commit();
                $this->em->clear();
            } catch (\Throwable $e) {
                $io->error($e->getMessage());
                $this->em->rollback();
            }
        }

        $io->success('Done!');

        return Command::SUCCESS;
    }
}
