<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product, bool $flush = false): void
    {
        $this->getEntityManager()->persist($product);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $product, bool $flush = false): void
    {
        $this->getEntityManager()->remove($product);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByIdAndBotIdentifier(int $id, string $botIdentifier): ?Product
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
     * @return Product[]
     */
    public function findAllByBotIdentifier(string $botIdentifier): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.bot_identifier = :botIdentifier')
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getResult();
    }
}
