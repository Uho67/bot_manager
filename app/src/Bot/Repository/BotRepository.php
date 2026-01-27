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
}
