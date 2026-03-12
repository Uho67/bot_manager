<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Repository;

use App\Mailout\Entity\PostMailout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostMailout>
 */
class PostMailoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostMailout::class);
    }

    /**
     * Bulk insert post mailout records
     *
     * @param array<array{chat_id: string, post_id: int}> $mailouts
     * @return int Number of inserted records
     */
    public function bulkInsertPostMailouts(string $botIdentifier, array $mailouts): int
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
            $params[] = $mailout['post_id'];
            $params[] = PostMailout::STATUS_PENDING;
            $params[] = $botIdentifier;
            $params[] = $nowStr;
        }

        $sql = sprintf(
            'INSERT INTO post_mailout (chat_id, post_id, status, bot_identifier, created_at)
             VALUES %s',
            implode(', ', $values)
        );

        $conn->executeStatement($sql, $params);

        return count($mailouts);
    }

    /**
     * Get distinct post IDs that have pending mailouts for a specific bot
     *
     * @return array<int>
     */
    public function getPostIdsByBotIdentifier(string $botIdentifier): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('DISTINCT m.post_id')
            ->where('m.bot_identifier = :botIdentifier')
            ->andWhere('m.status = :status')
            ->orderBy('m.post_id', 'ASC')
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('status', PostMailout::STATUS_PENDING);

        $results = $qb->getQuery()->getSingleColumnResult();

        return array_map('intval', $results);
    }

    /**
     * Find post mailouts by post IDs and bot identifier with limit
     *
     * @param array<int> $postIds
     * @return PostMailout[]
     */
    public function findByPostIdsAndBotIdentifier(array $postIds, string $botIdentifier, int $limit = 100): array
    {
        if (empty($postIds)) {
            return [];
        }

        return $this->createQueryBuilder('m')
            ->where('m.post_id IN (:postIds)')
            ->andWhere('m.bot_identifier = :botIdentifier')
            ->orderBy('m.created_at', 'ASC')
            ->setParameter('postIds', $postIds)
            ->setParameter('botIdentifier', $botIdentifier)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get statistics grouped by post_id for a specific bot
     *
     * @return array<int, array{post_id: int, total: int, sent: int, pending: int, failed: int}>
     */
    public function getAllStatistics(string $botIdentifier): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT
                post_id,
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'sent\' THEN 1 ELSE 0 END) AS sent,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = \'failed\' THEN 1 ELSE 0 END) AS failed
            FROM post_mailout
            WHERE bot_identifier = :botIdentifier
            GROUP BY post_id
            ORDER BY post_id ASC
        ';

        $rows = $conn->fetchAllAssociative($sql, ['botIdentifier' => $botIdentifier]);

        $result = [];
        foreach ($rows as $row) {
            $postId = (int) $row['post_id'];
            $result[$postId] = [
                'post_id' => $postId,
                'total' => (int) $row['total'],
                'sent' => (int) $row['sent'],
                'pending' => (int) $row['pending'],
                'failed' => (int) $row['failed'],
            ];
        }

        return $result;
    }

    /**
     * Delete all post mailouts for a specific post and bot
     *
     * @return int Number of deleted mailouts
     */
    public function deleteAllByPostAndBotIdentifier(int $postId, string $botIdentifier): int
    {
        return $this->createQueryBuilder('m')
            ->delete()
            ->where('m.post_id = :postId')
            ->andWhere('m.bot_identifier = :botIdentifier')
            ->setParameter('postId', $postId)
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->execute();
    }

    /**
     * Bulk delete post mailouts by IDs for a specific bot
     *
     * @param array<int> $mailoutIds
     * @return int Number of deleted mailouts
     */
    public function bulkDeletePostMailouts(string $botIdentifier, array $mailoutIds): int
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
