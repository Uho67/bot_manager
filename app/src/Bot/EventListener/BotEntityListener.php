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
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;

readonly class BotEntityListener
{
    public function __construct(
        private BotMediaService $mediaService,
        private LoggerInterface $logger
    ) {}

    /**
     * Called after a new Bot is persisted
     */
    public function postPersist(Bot $bot, PostPersistEventArgs $args): void
    {
        if ($bot->getApiKey()) {
            try {
                $this->mediaService->createBotFolders($bot->getApiKey());
                $this->logger->info('Media folders created for new bot', [
                    'bot_id' => $bot->getId(),
                    'bot_identifier' => $bot->getBotIdentifier(),
                    'folder' => $this->mediaService->getBotFolder($bot->getApiKey())
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Failed to create media folders for bot', [
                    'bot_id' => $bot->getId(),
                    'error' => $e->getMessage()
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
            $oldApiKey = $args->getOldValue('api_key');
            $newApiKey = $args->getNewValue('api_key');

            if ($oldApiKey && $newApiKey && $oldApiKey !== $newApiKey) {
                try {
                    // Move files from old folder to new folder
                    $this->mediaService->moveBotFolders($oldApiKey, $newApiKey);

                    $this->logger->info('Media folders renamed for bot', [
                        'bot_id' => $bot->getId(),
                        'old_folder' => $this->mediaService->getBotFolder($oldApiKey),
                        'new_folder' => $this->mediaService->getBotFolder($newApiKey)
                    ]);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to rename media folders for bot', [
                        'bot_id' => $bot->getId(),
                        'error' => $e->getMessage()
                    ]);
                    // Optionally: throw $e; if you want to prevent the update on error
                }
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
                    'folder' => $this->mediaService->getBotFolder($bot->getApiKey())
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete media folders for bot', [
                    'bot_id' => $bot->getId(),
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}

