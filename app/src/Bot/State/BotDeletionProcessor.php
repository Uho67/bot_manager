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
use Psr\Log\LoggerInterface;

readonly class BotDeletionProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $removeProcessor,
        private BotMediaService $mediaService,
        private LoggerInterface $logger
    ) {}

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Bot && $data->getApiKey()) {
            try {
                $apiKey = $data->getApiKey();
                $result = $this->removeProcessor->process($data, $operation, $uriVariables, $context);
                $this->mediaService->deleteBotFolders($apiKey);
                $this->logger->info('Media folders deleted for bot', [
                    'bot_id' => $data->getId(),
                    'bot_identifier' => $data->getBotIdentifier(),
                    'folder' => $this->mediaService->getBotFolder($apiKey)
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete media folders for bot', [
                    'bot_id' => $data->getId(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}

