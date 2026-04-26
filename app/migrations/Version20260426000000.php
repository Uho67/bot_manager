<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on post_mailout (post_id, chat_id, bot_identifier) to prevent duplicate queue entries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE pm1 FROM post_mailout pm1
            INNER JOIN post_mailout pm2
            WHERE pm1.id > pm2.id
              AND pm1.post_id = pm2.post_id
              AND pm1.chat_id = pm2.chat_id
              AND pm1.bot_identifier = pm2.bot_identifier');

        $this->addSql('CREATE UNIQUE INDEX uniq_post_mailout_post_chat_bot ON post_mailout (post_id, chat_id, bot_identifier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_post_mailout_post_chat_bot ON post_mailout');
    }
}
