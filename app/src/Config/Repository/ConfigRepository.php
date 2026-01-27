<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Repository;

use App\Config\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Config>
 */
class ConfigRepository extends ServiceEntityRepository
{
    private const string CACHE_KEY = 'config';

    public function __construct(
        ManagerRegistry $registry,
        private readonly CacheInterface $cache,
    ) {
        parent::__construct($registry, Config::class);
    }

    public function findByBotIdentifier(string $botIdentifier): array
    {
        try {
            return $this->cache->get(
                "{$botIdentifier}" . self::CACHE_KEY,
                function (ItemInterface $item) use ($botIdentifier) {
                    $m = 10;
                    $item->expiresAfter(3600);

                    return $this->createQueryBuilder('c')
                        ->where('c.bot_identifier = :botIdentifier')
                        ->setParameter('botIdentifier', $botIdentifier)
                        ->getQuery()
                        ->getResult();
                },
            );
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }

    public function findByBotIdentifierAndPath(string $botIdentifier, string $path): ?Config
    {
        return $this->createQueryBuilder('c')
            ->where('c.bot_identifier = :botIdentifier')
            ->andWhere('c.path = :path')
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('path', $path)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function clearCache(string $botIdentifier): void
    {
        $this->cache->delete("{$botIdentifier}" . self::CACHE_KEY);
    }
}
