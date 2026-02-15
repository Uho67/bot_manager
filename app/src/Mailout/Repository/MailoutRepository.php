<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Repository;

use App\Mailout\Entity\Mailout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mailout>
 */
class MailoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mailout::class);
    }

    /**
     * Bulk insert mailout records
     * 
     * @param array<array{chat_id: string, product_id: int}> $mailouts
     * @return int Number of inserted records
     */
    public function bulkInsertMailouts(string $botIdentifier, array $mailouts): int
    {
        if (empty($mailouts)) {
            return 0;
        }

        $conn = $this->getEntityManager()->getConnection();
        $now = new \DateTimeImmutable();
        $nowStr = $now->format('Y-m-d H:i:s');

        $values = [];
        $params = [];

        foreach ($mailouts as $mailout) {
            $values[] = '(?, ?, ?, ?, ?)';
            $params[] = $mailout['chat_id'];
            $params[] = $mailout['product_id'];
            $params[] = Mailout::STATUS_PENDING;
            $params[] = $botIdentifier;
            $params[] = $nowStr;
        }

        $sql = sprintf(
            'INSERT INTO mailout (chat_id, product_id, status, bot_identifier, created_at)
             VALUES %s',
            implode(', ', $values)
        );

        $conn->executeStatement($sql, $params);

        return count($mailouts);
    }

    /**
     * Get mailout statistics for a product
     * 
     * @return array{total: int, sent: int, pending: int, failed: int}
     */
    public function getStatisticsByProduct(int $productId, string $botIdentifier): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id) as total')
            ->addSelect('SUM(CASE WHEN m.status = :sent THEN 1 ELSE 0 END) as sent')
            ->addSelect('SUM(CASE WHEN m.status = :pending THEN 1 ELSE 0 END) as pending')
            ->addSelect('SUM(CASE WHEN m.status = :failed THEN 1 ELSE 0 END) as failed')
            ->where('m.product_id = :productId')
            ->andWhere('m.bot_identifier = :botIdentifier')
            ->setParameter('productId', $productId)
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('sent', Mailout::STATUS_SENT)
            ->setParameter('pending', Mailout::STATUS_PENDING)
            ->setParameter('failed', Mailout::STATUS_FAILED);

        $result = $qb->getQuery()->getSingleResult();

        return [
            'total' => (int) ($result['total'] ?? 0),
            'sent' => (int) ($result['sent'] ?? 0),
            'pending' => (int) ($result['pending'] ?? 0),
            'failed' => (int) ($result['failed'] ?? 0),
        ];
    }

    /**
     * Get all mailout statistics grouped by product
     * 
     * @return array<int, array{product_id: int, total: int, sent: int, pending: int, failed: int}>
     */
    public function getAllStatistics(string $botIdentifier): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.product_id')
            ->addSelect('COUNT(m.id) as total')
            ->addSelect('SUM(CASE WHEN m.status = :sent THEN 1 ELSE 0 END) as sent')
            ->addSelect('SUM(CASE WHEN m.status = :pending THEN 1 ELSE 0 END) as pending')
            ->addSelect('SUM(CASE WHEN m.status = :failed THEN 1 ELSE 0 END) as failed')
            ->where('m.bot_identifier = :botIdentifier')
            ->groupBy('m.product_id')
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('sent', Mailout::STATUS_SENT)
            ->setParameter('pending', Mailout::STATUS_PENDING)
            ->setParameter('failed', Mailout::STATUS_FAILED);

        $results = $qb->getQuery()->getResult();

        $statistics = [];
        foreach ($results as $result) {
            $statistics[$result['product_id']] = [
                'product_id' => (int) $result['product_id'],
                'total' => (int) ($result['total'] ?? 0),
                'sent' => (int) ($result['sent'] ?? 0),
                'pending' => (int) ($result['pending'] ?? 0),
                'failed' => (int) ($result['failed'] ?? 0),
            ];
        }

        return $statistics;
    }

    /**
     * Get distinct product IDs that have mailouts for a specific bot
     * 
     * @return array<int>
     */
    public function getProductIdsByBotIdentifier(string $botIdentifier): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('DISTINCT m.product_id')
            ->where('m.bot_identifier = :botIdentifier')
            ->orderBy('m.product_id', 'ASC')
            ->setParameter('botIdentifier', $botIdentifier);

        $results = $qb->getQuery()->getSingleColumnResult();

        return array_map('intval', $results);
    }

    /**
     * Find mailouts by product IDs and bot identifier with limit
     * 
     * @param array<int> $productIds
     * @return Mailout[]
     */
    public function findByProductIdsAndBotIdentifier(array $productIds, string $botIdentifier, int $limit = 100): array
    {
        if (empty($productIds)) {
            return [];
        }

        return $this->createQueryBuilder('m')
            ->where('m.product_id IN (:productIds)')
            ->andWhere('m.bot_identifier = :botIdentifier')
            ->orderBy('m.created_at', 'ASC')
            ->setParameter('productIds', $productIds)
            ->setParameter('botIdentifier', $botIdentifier)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Bulk delete mailouts by IDs for a specific bot
     * 
     * @param array<int> $mailoutIds
     * @return int Number of deleted mailouts
     */
    public function bulkDeleteMailouts(string $botIdentifier, array $mailoutIds): int
    {
        if (empty($mailoutIds)) {
            return 0;
        }

        $qb = $this->createQueryBuilder('m')
            ->delete()
            ->where('m.bot_identifier = :botIdentifier')
            ->andWhere('m.id IN (:ids)')
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('ids', $mailoutIds);

        return $qb->getQuery()->execute();
    }
}
