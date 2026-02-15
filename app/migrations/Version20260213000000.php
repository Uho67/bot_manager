<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create mailout table
 */
final class Version20260213000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create mailout table for tracking product mailouts to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mailout (
            id INT AUTO_INCREMENT NOT NULL,
            chat_id BIGINT NOT NULL,
            product_id INT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT \'pending\',
            bot_identifier VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            sent_at DATETIME NULL,
            INDEX idx_mailout_bot_identifier (bot_identifier),
            INDEX idx_mailout_product_id (product_id),
            INDEX idx_mailout_chat_id (chat_id),
            INDEX idx_mailout_status (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mailout');
    }
}
