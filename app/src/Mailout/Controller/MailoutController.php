<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Controller;

use App\Mailout\Repository\PostMailoutRepository;
use App\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/mailout')]
class MailoutController extends AbstractController
{
    public function __construct(
        private readonly PostMailoutRepository $postMailoutRepository,
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    #[Route('/send-post/{postId}', name: 'api_mailout_send_post', methods: ['POST'])]
    public function sendPostToAllUsers(int $postId, Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        // Get all active users for this bot
        $activeUsers = $this->userRepository->createQueryBuilder('u')
            ->where('u.bot_identifier = :botIdentifier')
            ->andWhere('u.status = :status')
            ->setParameter('botIdentifier', $botIdentifier)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();

        if (empty($activeUsers)) {
            return new JsonResponse([
                'message' => 'No active users found',
                'created' => 0,
            ]);
        }

        // Prepare post mailout records
        $mailouts = array_map(
            fn($user) => [
                'chat_id' => $user->getChatId(),
                'post_id' => $postId,
            ],
            $activeUsers
        );

        // Bulk insert post mailout records
        $created = $this->postMailoutRepository->bulkInsertPostMailouts($botIdentifier, $mailouts);

        return new JsonResponse([
            'message' => 'Post mailout records created successfully',
            'created' => $created,
            'total_users' => count($activeUsers),
        ]);
    }

    #[Route('/statistics', name: 'api_mailout_statistics', methods: ['GET'])]
    public function getStatistics(Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        $statistics = $this->postMailoutRepository->getAllStatistics($botIdentifier);

        return new JsonResponse($statistics);
    }

    private function getUserFromAuth()
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !\is_object($user) || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('Not authenticated');
        }

        return $user;
    }
}
