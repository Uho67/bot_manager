<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Button\EventListener;

use App\Button\Entity\Button;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

readonly class ButtonEntityListener
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * Set bot_identifier from JWT token before persisting new Button.
     */
    public function prePersist(Button $button, PrePersistEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$button->getBotIdentifier()) {
            $button->setBotIdentifier($user->getBotIdentifier());
        }
    }

    /**
     * Validate bot_identifier before updating Button.
     */
    public function preUpdate(Button $button, PreUpdateEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$button->getBotIdentifier()) {
            $button->setBotIdentifier($user->getBotIdentifier());
        }
    }
}
