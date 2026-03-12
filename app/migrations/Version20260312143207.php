<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312143207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_mailout (id INT AUTO_INCREMENT NOT NULL, chat_id BIGINT NOT NULL, post_id INT NOT NULL, status VARCHAR(50) DEFAULT \'pending\' NOT NULL, bot_identifier VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, sent_at DATETIME DEFAULT NULL, INDEX idx_post_mailout_bot_identifier (bot_identifier), INDEX idx_post_mailout_post_id (post_id), INDEX idx_post_mailout_chat_id (chat_id), INDEX idx_post_mailout_status (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX unique_user_chat_bot ON user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE post_mailout');
        $this->addSql('CREATE UNIQUE INDEX unique_user_chat_bot ON `user` (chat_id, bot_identifier)');
    }
}
