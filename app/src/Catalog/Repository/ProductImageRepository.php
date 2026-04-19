<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Entity\ProductImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage>
 */
class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }

    public function save(ProductImage $productImage, bool $flush = false): void
    {
        $this->getEntityManager()->persist($productImage);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductImage $productImage, bool $flush = false): void
    {
        $this->getEntityManager()->remove($productImage);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
