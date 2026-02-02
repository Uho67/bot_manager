<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add sort_order column to product and category tables
 */
final class Version20260131000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sort_order column to product and category tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD sort_order INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE category ADD sort_order INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP sort_order');
        $this->addSql('ALTER TABLE category DROP sort_order');
    }
}

