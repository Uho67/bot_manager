<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Entity\CategoryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryImage>
 */
class CategoryImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryImage::class);
    }

    public function save(CategoryImage $categoryImage, bool $flush = false): void
    {
        $this->getEntityManager()->persist($categoryImage);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CategoryImage $categoryImage, bool $flush = false): void
    {
        $this->getEntityManager()->remove($categoryImage);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
