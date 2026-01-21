<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121183648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE config (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, bot_identifier VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL, INDEX idx_bot_identifier (bot_identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE config ADD CONSTRAINT FK_D48A2F7CCD25455D FOREIGN KEY (bot_identifier) REFERENCES bot (bot_identifier) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config DROP FOREIGN KEY FK_D48A2F7CCD25455D');
        $this->addSql('DROP TABLE config');
    }
}
