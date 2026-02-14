<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Post\Repository;

use App\Post\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post, bool $flush = false): void
    {
        $this->getEntityManager()->persist($post);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $post, bool $flush = false): void
    {
        $this->getEntityManager()->remove($post);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByIdAndBotIdentifier(int $id, string $botIdentifier): ?Post
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.bot_identifier = :botIdentifier')
            ->setParameter('id', $id)
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Post[]
     */
    public function findAllByBotIdentifier(string $botIdentifier): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.bot_identifier = :botIdentifier')
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getResult();
    }

    public function findByTemplateTypeAndBotIdentifier(string $templateType, string $botIdentifier): ?Post
    {
        return $this->createQueryBuilder('p')
            ->where('p.template_type = :templateType')
            ->andWhere('p.bot_identifier = :botIdentifier')
            ->andWhere('p.enabled = :enabled')
            ->setParameter('templateType', $templateType)
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findEnabledByIdAndBotIdentifier(int $id, string $botIdentifier): ?Post
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.bot_identifier = :botIdentifier')
            ->andWhere('p.enabled = :enabled')
            ->setParameter('id', $id)
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
