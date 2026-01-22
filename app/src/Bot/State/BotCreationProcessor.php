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

readonly class BotCreationProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private BotMediaService $mediaService,
        private LoggerInterface $logger
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Bot) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }
        $isNew = $data->getId() === null;
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        if ($isNew && $data->getApiKey()) {
            try {
                $this->mediaService->createBotFolders($data->getApiKey());
                $this->logger->info('Media folders created for new bot', [
                    'bot_id' => $data->getId(),
                    'bot_identifier' => $data->getBotIdentifier(),
                    'folder' => $this->mediaService->getBotFolder($data->getApiKey())
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Failed to create media folders for bot', [
                    'bot_id' => $data->getId(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $result;
    }
}

