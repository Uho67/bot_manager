<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add text column to template table
 */
final class Version20260419000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add text column to template table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE template ADD text LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE template DROP COLUMN text');
    }
}
