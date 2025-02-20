<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250217073852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SET maintenance_work_mem = \'8GB\'');
        $this->addSql('CREATE INDEX idx_embedding_cosine ON news_post USING hnsw (embedding vector_cosine_ops);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_embedding_cosine');
    }
}
