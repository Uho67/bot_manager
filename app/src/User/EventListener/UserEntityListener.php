<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\User\EventListener;

use App\User\Entity\User;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

readonly class UserEntityListener
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * Set bot_identifier from JWT token before persisting new User.
     */
    public function prePersist(User $user, PrePersistEventArgs $args): void
    {
        $authUser = $this->security->getUser();
        if (!$authUser || !method_exists($authUser, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$user->getBotIdentifier()) {
            $user->setBotIdentifier($authUser->getBotIdentifier());
        }
    }

    /**
     * Validate bot_identifier before updating User.
     */
    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        $authUser = $this->security->getUser();
        if (!$authUser || !method_exists($authUser, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$user->getBotIdentifier()) {
            $user->setBotIdentifier($authUser->getBotIdentifier());
        }
    }
}
