<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260202210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create template table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE template (id INT AUTO_INCREMENT NOT NULL, bot_identifier VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL, layout JSON NOT NULL, INDEX idx_template_bot_identifier (bot_identifier), INDEX idx_template_type (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE template');
    }
}

