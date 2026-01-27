<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Bot\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class BotMediaService
{
    private string $mediaPath;

    private Filesystem $filesystem;

    public function __construct(
        string $projectDir,
        private readonly LoggerInterface $logger,
    ) {
        $this->mediaPath = $projectDir . '/public/media/telegram';
        $this->filesystem = new Filesystem();
    }

    public function getBotFolder(string $apiKey): string
    {
        return substr($apiKey, 0, 20);
    }

    /**
     * Create bot media folders on bot creation
     */
    public function createBotFolders(string $apiKey): void
    {
        $botFolder = $this->getBotFolder($apiKey);
        $basePath = "{$this->mediaPath}/{$botFolder}";
        $folders = [
            $basePath,
            "{$basePath}/photos",
        ];

        foreach ($folders as $folder) {
            if (!$this->filesystem->exists($folder)) {
                $this->filesystem->mkdir($folder, 0o755);
                $this->logger->info("Created folder: {$folder}");
            }
        }

        $this->logger->info('Bot media folders created', [
            'bot_folder' => $botFolder,
            'base_path' => $basePath,
        ]);
    }

    /**
     * Save photo file
     */
    public function savePhoto(string $apiKey, string $fileContent, string $filename): string
    {
        $botFolder = $this->getBotFolder($apiKey);
        $photoPath = "{$this->mediaPath}/{$botFolder}/photos";

        if (!$this->filesystem->exists($photoPath)) {
            $this->createBotFolders($apiKey);
        }

        $fullPath = "{$photoPath}/{$filename}";
        $this->filesystem->dumpFile($fullPath, $fileContent);

        $this->logger->info('Photo saved', [
            'bot_folder' => $botFolder,
            'filename' => $filename,
        ]);

        return "/media/telegram/{$botFolder}/photos/{$filename}";
    }

    /**
     * Get public URL for file
     */
    public function getPublicUrl(string $apiKey, string $type, string $filename): string
    {
        $botFolder = $this->getBotFolder($apiKey);

        return "/media/telegram/{$botFolder}/{$type}/{$filename}";
    }

    public function moveBotFolders(string $oldApiKey, string $newApiKey): void
    {
        $oldBotFolder = $this->getBotFolder($oldApiKey);
        $newBotFolder = $this->getBotFolder($newApiKey);

        $oldPath = "{$this->mediaPath}/{$oldBotFolder}";
        $newPath = "{$this->mediaPath}/{$newBotFolder}";

        if ($this->filesystem->exists($oldPath)) {
            // Rename/move the entire folder
            $this->filesystem->rename($oldPath, $newPath);

            $this->logger->info('Bot media folders moved', [
                'old_folder' => $oldBotFolder,
                'new_folder' => $newBotFolder,
                'old_path' => $oldPath,
                'new_path' => $newPath,
            ]);
        } else {
            $this->createBotFolders($newApiKey);
        }
    }

    public function deleteBotFolders(string $apiKey): void
    {
        $botFolder = $this->getBotFolder($apiKey);
        $basePath = "{$this->mediaPath}/{$botFolder}";

        if ($this->filesystem->exists($basePath)) {
            $this->filesystem->remove($basePath);
            $this->logger->info('Bot media folders deleted', [
                'bot_folder' => $botFolder,
                'base_path' => $basePath,
            ]);
        }
    }

    public function getBotMediaPath(string $apiKey, string $type = ''): string
    {
        $botFolder = $this->getBotFolder($apiKey);
        $path = "{$this->mediaPath}/{$botFolder}";

        return $type ? "{$path}/{$type}" : $path;
    }
}
