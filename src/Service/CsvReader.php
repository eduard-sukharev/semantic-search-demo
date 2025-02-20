<?php

declare(strict_types=1);

namespace App\Service;


use function Symfony\Component\VarDumper\Dumper\esc;

final class CsvReader
{
    private $fd;
    public function __construct($filename){
        ini_set('auto_detect_line_endings',true);
        $this->fd = fopen($filename, 'rb');
    }
    public function __destruct(){
        ini_set('auto_detect_line_endings',false);
        if ($this->fd) {
            fclose($this->fd);
        }
    }

    public function getRows(): \Generator
    {
        $header = [];
        $row = [];
        while (($row = fgetcsv($this->fd, escape: '')) !== false)
        {
            if(!$header){
                $header = $row;
            } else {
                try {
                    yield array_combine($header, $row);
                } catch (\Throwable $e) {
                    var_dump($header);
                    var_dump($row);
                }
            }

        }
//        fclose($this->fd);


//        $header = [];
//        $row = [];
//        while (($line = fgets($this->fd)) !== false)
//        {
//            if ($header && !str_starts_with($line, '2014-')) {
//                $row['text'] .= $line;
//                continue;
//            }
//            if(!$header){
//                $header = array_map(trim(...), explode(",", $line, 6));
//            } else {
//                yield array_combine($header, $row);
//                $row = array_map(trim(...), explode(",", $line, 6));
//            }
//
//            var_dump($row);
//        }
//        fclose($this->fd);
    }
}
