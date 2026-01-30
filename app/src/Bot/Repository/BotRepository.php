<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Bot\Repository;

use App\Bot\Entity\Bot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bot>
 */
class BotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bot::class);
    }

    /**
     * Find a bot by verifying the plain API key against stored hashed keys
     */
    public function findByApiKey(string $plainApiKey): ?Bot
    {
        // Get all bots and verify the API key against each hashed key
        $bots = $this->findAll();

        foreach ($bots as $bot) {
            if (password_verify($plainApiKey, $bot->getApiKey())) {
                return $bot;
            }
        }

        return null;
    }
}
