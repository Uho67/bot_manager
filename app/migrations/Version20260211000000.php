<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create user table
 */
final class Version20260211000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            bot_identifier VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            username VARCHAR(255) NOT NULL,
            chat_id BIGINT NOT NULL,
            status VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_user_bot_identifier (bot_identifier),
            INDEX idx_user_chat_id (chat_id),
            INDEX idx_user_status (status),
            INDEX idx_user_created_at (created_at),
            INDEX idx_user_updated_at (updated_at),
            UNIQUE KEY unique_user_chat_bot (chat_id, bot_identifier),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `user`');
    }
}
