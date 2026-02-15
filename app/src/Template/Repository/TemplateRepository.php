<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Template\Repository;

use App\Template\Entity\Template;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Template>
 */
class TemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }

    public function save(Template $template, bool $flush = false): void
    {
        $this->getEntityManager()->persist($template);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Template $template, bool $flush = false): void
    {
        $this->getEntityManager()->remove($template);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByIdAndBotIdentifier(int $id, string $botIdentifier): ?Template
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->andWhere('t.bot_identifier = :botIdentifier')
            ->setParameter('id', $id)
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Template[]
     */
    public function findAllByBotIdentifier(string $botIdentifier): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.bot_identifier = :botIdentifier')
            ->setParameter('botIdentifier', $botIdentifier)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFirstByTypeAndBotIdentifier(string $type, string $botIdentifier): ?Template
    {
        return $this->createQueryBuilder('t')
            ->where('t.type = :type')
            ->andWhere('t.bot_identifier = :botIdentifier')
            ->setParameter('type', $type)
            ->setParameter('botIdentifier', $botIdentifier)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Template[]
     */
    public function findByTypeAndBotIdentifier(string $type, string $botIdentifier): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.type = :type')
            ->andWhere('t.bot_identifier = :botIdentifier')
            ->setParameter('type', $type)
            ->setParameter('botIdentifier', $botIdentifier)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

