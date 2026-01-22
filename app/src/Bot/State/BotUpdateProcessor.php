<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Bot\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Bot\Entity\Bot;
use App\Bot\Service\BotMediaService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Processor that handles media folders when bot is updated (PUT/PATCH)
 */
readonly class BotUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private BotMediaService $mediaService,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Bot || !$data->getId()) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        // Get original data before changes
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($data);

        $oldApiKey = $originalData['api_key'] ?? null;
        $newApiKey = $data->getApiKey();

        // Check if API key changed
        if ($oldApiKey && $newApiKey && $oldApiKey !== $newApiKey) {
            try {
                // Move files from old folder to new folder
                $this->mediaService->moveBotFolders($oldApiKey, $newApiKey);

                $this->logger->info('Media folders renamed for bot', [
                    'bot_id' => $data->getId(),
                    'old_folder' => $this->mediaService->getBotFolder($oldApiKey),
                    'new_folder' => $this->mediaService->getBotFolder($newApiKey)
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Failed to rename media folders for bot', [
                    'bot_id' => $data->getId(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        // Persist the changes
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}

