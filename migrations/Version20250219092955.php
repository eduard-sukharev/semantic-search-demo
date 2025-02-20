<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250219092955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news_post RENAME COLUMN embedding TO embedding_enbeddrus');
        $this->addSql('ALTER TABLE news_post ADD embedding_arctic_2 vector(1024)');
        $this->addSql('ALTER TABLE news_post ADD embedding_bge_m3 vector(1024)');
        $this->addSql('ALTER TABLE news_post DROP date');
        $this->addSql('ALTER TABLE news_post DROP source_type');
        $this->addSql('ALTER TABLE news_post DROP source_name');
        $this->addSql('ALTER TABLE news_post DROP chunk_number');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news_post RENAME COLUMN embedding_enbeddrus TO embedding');
        $this->addSql('ALTER TABLE news_post DROP embedding_arctic_2');
        $this->addSql('ALTER TABLE news_post DROP embedding_bge_m3');
        $this->addSql('ALTER TABLE news_post ADD source_type TEXT NOT NULL DEFAULT \'manual\'');
        $this->addSql('ALTER TABLE news_post ADD source_name TEXT NOT NULL DEFAULT \'manual\'');
        $this->addSql('ALTER TABLE news_post ADD chunk_number INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE news_post ADD date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()');
    }
}
