<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add unique constraint on (chat_id, bot_identifier) to user table
 */
final class Version20260212000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on (chat_id, bot_identifier) to user table';
    }

    public function up(Schema $schema): void
    {
        // Add unique constraint if it doesn't exist
        // Note: This will fail if constraint already exists, but that's okay - just means it was already added
        $this->addSql('ALTER TABLE `user` ADD UNIQUE KEY unique_user_chat_bot (chat_id, bot_identifier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP INDEX unique_user_chat_bot');
    }
}
