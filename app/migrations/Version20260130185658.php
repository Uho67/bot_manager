<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to hash existing plain API keys in the bot table using SHA256
 */
final class Version20260130185658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Hash existing plain API keys in bot table using SHA256 to match Node.js';
    }

    public function up(Schema $schema): void
    {
        // Get all bots with their current API keys
        $bots = $this->connection->fetchAllAssociative('SELECT id, api_key FROM bot');

        foreach ($bots as $bot) {
            $plainApiKey = $bot['api_key'];

            // Check if the API key is already hashed (SHA256 is 64 chars hex)
            if (strlen($plainApiKey) !== 64 || !ctype_xdigit($plainApiKey)) {
                // Hash the plain API key with SHA256
                $hashedApiKey = hash('sha256', $plainApiKey);

                // Update the bot with the hashed API key
                $this->addSql(
                    'UPDATE bot SET api_key = :hashed_key WHERE id = :id',
                    ['hashed_key' => $hashedApiKey, 'id' => $bot['id']]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Note: It's not possible to reverse hash to plain text
        // This migration is irreversible
        $this->addSql('SELECT 1'); // Placeholder to prevent empty migration
    }
}
