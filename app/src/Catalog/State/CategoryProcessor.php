<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Catalog\Entity\Category;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Processor that sets bot_identifier from JWT token for Category
 */
class CategoryProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security,
        private readonly LoggerInterface $logger
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Category) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $this->logger->info('CategoryProcessor received data', [
            'category_id' => $data->getId(),
            'category_name' => $data->getName(),
            'childCategories_count' => $data->getChildCategories()->count(),
        ]);

        // Get current user from JWT
        $user = $this->security->getUser();

        if ($user && method_exists($user, 'getBotIdentifier')) {
            $botIdentifier = $user->getBotIdentifier();

            // Set bot_identifier only if not already set (for new categories)
            if (!$data->getBotIdentifier()) {
                $data->setBotIdentifier($botIdentifier);

                $this->logger->info('Bot identifier set for category', [
                    'bot_identifier' => $botIdentifier,
                    'category_name' => $data->getName()
                ]);
            }
        }

        // childCategories is now the owning side, so it will be persisted automatically
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}

