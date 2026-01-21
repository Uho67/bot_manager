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
    public function __construct(
        ManagerRegistry $registry,
        private readonly CacheInterface $cache
    ) {
        parent::__construct($registry, Config::class);
    }

    public function findByBotIdentifier(string $botIdentifier): array
    {
        try {
            return $this->cache->get(
                "configs.bot.{$botIdentifier}",
                function (ItemInterface $item) use ($botIdentifier) {
                    $item->expiresAfter(3600);

                    return $this->createQueryBuilder('c')
                        ->where('c.bot_identifier = :botIdentifier')
                        ->setParameter('botIdentifier', $botIdentifier)
                        ->getQuery()
                        ->getResult();
                }
            );
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }

    public function findByBotIdentifierAndPath(string $botIdentifier, string $path): ?Config
    {
        try {
            return $this->cache->get(
                "config.{$botIdentifier}.{$path}",
                function (ItemInterface $item) use ($botIdentifier, $path) {
                    $item->expiresAfter(3600);

                    return $this->createQueryBuilder('c')
                        ->where('c.bot_identifier = :botIdentifier')
                        ->andWhere('c.path = :path')
                        ->setParameter('botIdentifier', $botIdentifier)
                        ->setParameter('path', $path)
                        ->getQuery()
                        ->getOneOrNullResult();
                }
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function clearCache(string $botIdentifier): void
    {
        try {
            $this->cache->delete("configs.bot.{$botIdentifier}");

            // Clear individual config caches
            $configs = $this->createQueryBuilder('c')
                ->where('c.bot_identifier = :botIdentifier')
                ->setParameter('botIdentifier', $botIdentifier)
                ->getQuery()
                ->getResult();

            foreach ($configs as $config) {
                $this->cache->delete("config.{$botIdentifier}.{$config->getPath()}");
            }
        } catch (InvalidArgumentException $e) {
            // Ignore cache errors
        }
    }
}

