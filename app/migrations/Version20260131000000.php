<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add image_file_id column to product table
 */
final class Version20260131000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_file_id column to product table for storing Telegram file IDs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD image_file_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP image_file_id');
    }
}

