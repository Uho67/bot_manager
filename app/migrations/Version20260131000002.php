<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add is_root column to category table
 */
final class Version20260131000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_root column to category table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category ADD is_root TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category DROP is_root');
    }
}
