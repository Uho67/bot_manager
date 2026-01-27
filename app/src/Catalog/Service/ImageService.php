<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Service;

use App\Bot\Repository\BotRepository;
use App\Bot\Service\BotMediaService;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ImageService
{
    public function __construct(
        private BotMediaService $botMediaService,
        private BotRepository $botRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function uploadImage(UploadedFile $file, string $botIdentifier, string $entityCode): string
    {
        // Get bot by identifier to retrieve API key
        $bot = $this->botRepository->findOneBy(['bot_identifier' => $botIdentifier]);

        if (!$bot) {
            throw new RuntimeException("Bot not found for identifier: {$botIdentifier}");
        }

        $apiKey = $bot->getApiKey();

        // Generate unique filename
        $filename = uniqid($entityCode . '_', true) . '.' . $file->guessExtension();

        // Read file content
        $fileContent = file_get_contents($file->getPathname());

        // Save photo using BotMediaService
        $publicPath = $this->botMediaService->savePhoto($apiKey, $fileContent, $filename);

        $this->logger->info('Product image uploaded', [
            'bot_identifier' => $botIdentifier,
            'filename' => $filename,
            'public_path' => $publicPath,
        ]);

        return $publicPath;
    }

    /**
     * Delete product image
     */
    public function deleteImage(string $imagePath): void
    {
        // Extract bot folder and filename from path
        // Path format: /media/telegram/{botFolder}/photos/{filename}
        if (preg_match('#/media/telegram/([^/]+)/photos/(.+)#', $imagePath, $matches)) {
            $botFolder = $matches[1];
            $filename = $matches[2];

            $projectDir = \dirname(__DIR__, 3);
            $fullPath = "{$projectDir}/public/media/telegram/{$botFolder}/photos/{$filename}";

            if (file_exists($fullPath)) {
                unlink($fullPath);
                $this->logger->info('Product image deleted', [
                    'path' => $fullPath,
                ]);
            }
        }
    }
}
