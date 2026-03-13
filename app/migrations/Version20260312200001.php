<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Restore unique constraint on (chat_id, bot_identifier) and remove duplicate users
 */
final class Version20260312200001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore unique constraint on (chat_id, bot_identifier) and remove duplicate users (keep newest updated_at)';
    }

    public function up(Schema $schema): void
    {
        // Remove duplicate rows, keeping the one with the highest id (most recently inserted)
        // for each (chat_id, bot_identifier) pair
        $this->addSql('
            DELETE u1 FROM `user` u1
            INNER JOIN `user` u2
                ON u1.chat_id = u2.chat_id
                AND u1.bot_identifier = u2.bot_identifier
                AND u1.id < u2.id
        ');

        // Restore the unique constraint
        $this->addSql('CREATE UNIQUE INDEX unique_user_chat_bot ON `user` (chat_id, bot_identifier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX unique_user_chat_bot ON `user`');
    }
}
