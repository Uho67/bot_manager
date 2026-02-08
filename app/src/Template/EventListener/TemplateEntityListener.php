<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Template\EventListener;

use App\Template\Entity\Template;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

readonly class TemplateEntityListener
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * Set bot_identifier from JWT token before persisting new Template.
     */
    public function prePersist(Template $template, PrePersistEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$template->getBotIdentifier()) {
            $template->setBotIdentifier($user->getBotIdentifier());
        }
    }

    /**
     * Validate bot_identifier before updating Template.
     */
    public function preUpdate(Template $template, PreUpdateEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$template->getBotIdentifier()) {
            $template->setBotIdentifier($user->getBotIdentifier());
        }
    }
}

