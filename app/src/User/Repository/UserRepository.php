<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByIdAndBotIdentifier(int $id, string $botIdentifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->andWhere('u.bot_identifier = :botIdentifier')
            ->setParameter('id', $id)
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByChatIdAndBotIdentifier(string $chatId, string $botIdentifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.chat_id = :chatId')
            ->andWhere('u.bot_identifier = :botIdentifier')
            ->setParameter('chatId', $chatId)
            ->setParameter('botIdentifier', $botIdentifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return User[]
     */
    public function findAllByBotIdentifier(string $botIdentifier): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.bot_identifier = :botIdentifier')
            ->setParameter('botIdentifier', $botIdentifier)
            ->orderBy('u.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string, mixed> $filters
     * @return User[]
     */
    public function findByBotIdentifierWithFilters(string $botIdentifier, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.bot_identifier = :botIdentifier')
            ->setParameter('botIdentifier', $botIdentifier);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $qb->andWhere('u.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (isset($filters['created_at_from']) && $filters['created_at_from'] !== '') {
            try {
                $dateFrom = new \DateTimeImmutable($filters['created_at_from']);
                $qb->andWhere('u.created_at >= :created_at_from')
                    ->setParameter('created_at_from', $dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }

        if (isset($filters['created_at_to']) && $filters['created_at_to'] !== '') {
            try {
                $dateTo = new \DateTimeImmutable($filters['created_at_to']);
                $qb->andWhere('u.created_at <= :created_at_to')
                    ->setParameter('created_at_to', $dateTo);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }

        if (isset($filters['updated_at_from']) && $filters['updated_at_from'] !== '') {
            try {
                $dateFrom = new \DateTimeImmutable($filters['updated_at_from']);
                $qb->andWhere('u.updated_at >= :updated_at_from')
                    ->setParameter('updated_at_from', $dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }

        if (isset($filters['updated_at_to']) && $filters['updated_at_to'] !== '') {
            try {
                $dateTo = new \DateTimeImmutable($filters['updated_at_to']);
                $qb->andWhere('u.updated_at <= :updated_at_to')
                    ->setParameter('updated_at_to', $dateTo);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }

        $qb->orderBy('u.created_at', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Bulk upsert users using native SQL for better performance
     * Requires unique constraint on (chat_id, bot_identifier)
     * 
     * @param array<int, array{chat_id?: mixed, name?: mixed, username?: mixed, status?: mixed}> $usersData
     * @return array{created: int, updated: int, errors: array<string>}
     */
    public function bulkUpsertUsers(string $botIdentifier, array $usersData): array
    {
        if (empty($usersData)) {
            return ['created' => 0, 'updated' => 0, 'errors' => []];
        }

        $conn = $this->getEntityManager()->getConnection();
        $now = new \DateTimeImmutable();
        $nowStr = $now->format('Y-m-d H:i:s');

        $errors = [];
        $validUsers = [];

        // Validate and prepare users data
        foreach ($usersData as $index => $userData) {
            if (!isset($userData['chat_id'])) {
                $errors[] = "User at index {$index}: chat_id is required";
                continue;
            }

            $validUsers[] = [
                'chat_id' => (string) $userData['chat_id'],
                'name' => $userData['name'] ?? '',
                'username' => $userData['username'] ?? '',
                'status' => $userData['status'] ?? 'active',
            ];
        }

        if (empty($validUsers)) {
            return ['created' => 0, 'updated' => 0, 'errors' => $errors];
        }

        // Get existing chat_ids to count updates
        $chatIds = array_map(fn($u) => (string) $u['chat_id'], $validUsers);
        $existingChatIds = $this->createQueryBuilder('u')
            ->select('u.chat_id')
            ->where('u.bot_identifier = :botIdentifier')
            ->andWhere('u.chat_id IN (:chatIds)')
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('chatIds', $chatIds)
            ->getQuery()
            ->getSingleColumnResult();
        
        $updatedCount = count($existingChatIds);

        // Build VALUES clause with placeholders
        $values = [];
        $params = [];
        $paramIndex = 0;

        foreach ($validUsers as $userData) {
            $values[] = '(?, ?, ?, ?, ?, ?, ?)';
            $params[] = $userData['chat_id'];
            $params[] = $botIdentifier;
            $params[] = $userData['name'];
            $params[] = $userData['username'];
            $params[] = $userData['status'];
            $params[] = $nowStr;
            $params[] = $nowStr;
        }

        // Use INSERT ... ON DUPLICATE KEY UPDATE
        // This will insert new users or update existing ones based on unique constraint (chat_id, bot_identifier)
        $sql = sprintf(
            'INSERT INTO `user` (chat_id, bot_identifier, name, username, status, created_at, updated_at)
             VALUES %s
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                username = VALUES(username),
                status = VALUES(status),
                updated_at = VALUES(updated_at),
                created_at = created_at',
            implode(', ', $values)
        );

        // Execute the query
        $conn->executeStatement($sql, $params);

        $total = count($validUsers);
        $created = $total - $updatedCount;

        return [
            'created' => max(0, $created),
            'updated' => $updatedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Bulk delete users by IDs for a specific bot
     * 
     * @param array<int> $userIds
     * @return int Number of deleted users
     */
    public function bulkDeleteUsers(string $botIdentifier, array $userIds): int
    {
        if (empty($userIds)) {
            return 0;
        }

        $qb = $this->createQueryBuilder('u')
            ->delete()
            ->where('u.bot_identifier = :botIdentifier')
            ->andWhere('u.id IN (:ids)')
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('ids', $userIds);

        return $qb->getQuery()->execute();
    }
}
