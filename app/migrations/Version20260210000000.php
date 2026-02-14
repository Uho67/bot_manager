<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create post table
 */
final class Version20260210000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create post table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, bot_identifier VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, image_file_id VARCHAR(255) DEFAULT NULL, template_type VARCHAR(20) NOT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, INDEX idx_post_bot_identifier (bot_identifier), INDEX idx_post_template_type (template_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE post');
    }
}
