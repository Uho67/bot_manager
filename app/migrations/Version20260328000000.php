<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add remove_mode column to post_mailout table
 */
final class Version20260328000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add remove_mode column to post_mailout (remove|not_remove), default remove';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE post_mailout ADD remove_mode VARCHAR(20) NOT NULL DEFAULT 'remove'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post_mailout DROP COLUMN remove_mode');
    }
}
