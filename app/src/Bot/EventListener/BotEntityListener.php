<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Bot\EventListener;

use App\Bot\Entity\Bot;
use App\Bot\Service\BotMediaService;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Exception;
use Psr\Log\LoggerInterface;

readonly class BotEntityListener
{
    public function __construct(
        private BotMediaService $mediaService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Called before a new Bot is persisted - hash the API key
     */
    public function prePersist(Bot $bot, PrePersistEventArgs $args): void
    {
        // If api_key is provided, hash it and store plain version temporarily
        if ($bot->getApiKey()) {
            $plainApiKey = $bot->getApiKey();
            $hashedApiKey = password_hash($plainApiKey, PASSWORD_BCRYPT);

            // Store plain key temporarily for folder creation
            $bot->setPlainApiKey($plainApiKey);
            // Set hashed key for database storage
            $bot->setApiKey($hashedApiKey);

            $this->logger->info('API key hashed for new bot', [
                'bot_identifier' => $bot->getBotIdentifier(),
            ]);
        }
    }

    /**
     * Called after a new Bot is persisted
     */
    public function postPersist(Bot $bot, PostPersistEventArgs $args): void
    {
        // Use plain API key for folder creation
        $plainApiKey = $bot->getPlainApiKey();

        if ($plainApiKey) {
            try {
                $this->mediaService->createBotFolders($plainApiKey);
                $this->logger->info('Media folders created for new bot', [
                    'bot_id' => $bot->getId(),
                    'bot_identifier' => $bot->getBotIdentifier(),
                    'folder' => $this->mediaService->getBotFolder($plainApiKey),
                ]);
            } catch (Exception $e) {
                $this->logger->error('Failed to create media folders for bot', [
                    'bot_id' => $bot->getId(),
                    'error' => $e->getMessage(),
                ]);
            }

            // Clear plain API key from memory
            $bot->setPlainApiKey(null);
        }
    }

    /**
     * Called before a Bot is updated
     */
    public function preUpdate(Bot $bot, PreUpdateEventArgs $args): void
    {
        // Check if API key has changed
        if ($args->hasChangedField('api_key')) {
            $oldHashedApiKey = $args->getOldValue('api_key');
            $newPlainApiKey = $args->getNewValue('api_key');

            // Check if the new value is not already hashed (to prevent double hashing)
            if ($newPlainApiKey && !password_get_info($newPlainApiKey)['algo']) {
                // Hash the new API key
                $newHashedApiKey = password_hash($newPlainApiKey, PASSWORD_BCRYPT);

                // Store plain key temporarily for folder operations
                $bot->setPlainApiKey($newPlainApiKey);
                // Update with hashed key
                $bot->setApiKey($newHashedApiKey);

                $this->logger->info('API key hashed for bot update', [
                    'bot_id' => $bot->getId(),
                ]);

                // Note: Folder renaming will be handled in postUpdate if needed
                // For now, we'll skip folder renaming as it's complex with hashed keys
                // The folders are identified by plain API key, but we only store hashed version
            }
        }
    }

    /**
     * Called after a Bot is removed
     */
    public function postRemove(Bot $bot, PostRemoveEventArgs $args): void
    {
        if ($bot->getApiKey()) {
            try {
                $this->mediaService->deleteBotFolders($bot->getApiKey());
                $this->logger->info('Media folders deleted for bot', [
                    'bot_id' => $bot->getId(),
                    'bot_identifier' => $bot->getBotIdentifier(),
                    'folder' => $this->mediaService->getBotFolder($bot->getApiKey()),
                ]);
            } catch (Exception $e) {
                $this->logger->error('Failed to delete media folders for bot', [
                    'bot_id' => $bot->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
