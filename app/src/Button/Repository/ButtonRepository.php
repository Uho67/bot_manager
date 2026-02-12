<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Button\Repository;

use App\Button\Entity\Button;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Button>
 */
class ButtonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Button::class);
    }

    public function save(Button $button, bool $flush = false): void
    {
        $this->getEntityManager()->persist($button);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Button $button, bool $flush = false): void
    {
        $this->getEntityManager()->remove($button);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByIdAndBotIdentifier(int $id, string $botIdentifier): ?Button
    {
        return $this->createQueryBuilder('b')
            ->where('b.id = :id')
            ->andWhere('b.bot_identifier = :botIdentifier')
            ->setParameter('id', $id)
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Button[]
     */
    public function findAllByBotIdentifier(string $botIdentifier): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.bot_identifier = :botIdentifier')
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getResult();
    }
}
