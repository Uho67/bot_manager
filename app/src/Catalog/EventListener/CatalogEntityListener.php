<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\EventListener;

use App\Catalog\Entity\Category;
use App\Catalog\Entity\Product;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CatalogEntityListener
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * Set bot_identifier from JWT token before persisting new Category or Product
     */
    public function prePersist(Category|Product $catalogEntity, PrePersistEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$catalogEntity->getBotIdentifier()) {
            $catalogEntity->setBotIdentifier($user->getBotIdentifier());
        }
    }

    /**
     * Validate bot_identifier before updating Category or Product
     */
    public function preUpdate(Category|Product $catalogEntity, PreUpdateEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$catalogEntity->getBotIdentifier()) {
            $catalogEntity->setBotIdentifier($user->getBotIdentifier());
        }
    }
}


