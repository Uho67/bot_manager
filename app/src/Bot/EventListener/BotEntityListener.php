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
use Doctrine\ORM\Event\PostUpdateEventArgs;
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
     * Called before a new Bot is persisted - hash the API key with SHA256
     */
    public function prePersist(Bot $bot, PrePersistEventArgs $args): void
    {
        // If api_key is provided, hash it with SHA256 to match Node.js
        if ($bot->getApiKey()) {
            $plainApiKey = $bot->getApiKey();
            $hashedApiKey = hash('sha256', $plainApiKey);

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
        // Use hashed API key for folder creation (first 20 chars)
        $hashedApiKey = $bot->getApiKey();

        if ($hashedApiKey) {
            try {
                $this->mediaService->createBotFolders($hashedApiKey);
                $this->logger->info('Media folders created for new bot', [
                    'bot_id' => $bot->getId(),
                    'bot_identifier' => $bot->getBotIdentifier(),
                    'folder' => $this->mediaService->getBotFolder($hashedApiKey),
                ]);
            } catch (Exception $e) {
                $this->logger->error('Failed to create media folders for bot', [
                    'bot_id' => $bot->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
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
            $newValue = $args->getNewValue('api_key');

            // Check if the new value is not already hashed (SHA256 is 64 chars hex)
            if ($newValue && !ctype_xdigit($newValue) || strlen($newValue) !== 64) {
                // Hash the new API key with SHA256
                $newHashedApiKey = hash('sha256', $newValue);

                // Store old hashed key temporarily for folder renaming
                $bot->setPlainApiKey($oldHashedApiKey);
                // Update with new hashed key
                $bot->setApiKey($newHashedApiKey);

                $this->logger->info('API key hashed for bot update', [
                    'bot_id' => $bot->getId(),
                ]);
            }
        }
    }

    /**
     * Called after a Bot is updated
     */
    public function postUpdate(Bot $bot, PostUpdateEventArgs $args): void
    {
        // Check if we need to rename folders (plainApiKey contains old hashed key)
        $oldHashedApiKey = $bot->getPlainApiKey();
        $newHashedApiKey = $bot->getApiKey();

        if ($oldHashedApiKey && $newHashedApiKey && $oldHashedApiKey !== $newHashedApiKey) {
            try {
                $this->mediaService->moveBotFolders($oldHashedApiKey, $newHashedApiKey);
                $this->logger->info('Media folders renamed for bot', [
                    'bot_id' => $bot->getId(),
                    'old_folder' => $this->mediaService->getBotFolder($oldHashedApiKey),
                    'new_folder' => $this->mediaService->getBotFolder($newHashedApiKey),
                ]);
            } catch (Exception $e) {
                $this->logger->error('Failed to rename media folders for bot', [
                    'bot_id' => $bot->getId(),
                    'error' => $e->getMessage(),
                ]);
            }

            // Clear temporary storage
            $bot->setPlainApiKey(null);
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
