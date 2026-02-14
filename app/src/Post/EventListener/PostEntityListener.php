<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Post\EventListener;

use App\Post\Entity\Post;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

readonly class PostEntityListener
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * Set bot_identifier from JWT token before persisting new Post.
     */
    public function prePersist(Post $post, PrePersistEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$post->getBotIdentifier()) {
            $post->setBotIdentifier($user->getBotIdentifier());
        }
    }

    /**
     * Validate bot_identifier before updating Post.
     */
    public function preUpdate(Post $post, PreUpdateEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('User must have a bot identifier');
        }
        if (!$post->getBotIdentifier()) {
            $post->setBotIdentifier($user->getBotIdentifier());
        }
    }
}
