<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209095147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS vector;');
        $this->addSql('ALTER TABLE news_post ADD embedding vector(768)');
        $this->addSql('ALTER TABLE news_post ADD source_type TEXT NOT NULL DEFAULT \'manual\'');
        $this->addSql('ALTER TABLE news_post ADD source_name TEXT NOT NULL DEFAULT \'manual\'');
        $this->addSql('ALTER TABLE news_post ADD chunk_number INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE news_post RENAME COLUMN text TO content');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news_post RENAME COLUMN content TO text');
        $this->addSql('ALTER TABLE news_post DROP embedding');
        $this->addSql('ALTER TABLE news_post DROP source_type');
        $this->addSql('ALTER TABLE news_post DROP source_name');
        $this->addSql('ALTER TABLE news_post DROP chunk_number');
    }
}
