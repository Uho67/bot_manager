<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260209214500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add layout field to category table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category ADD layout JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category DROP layout');
    }
}

