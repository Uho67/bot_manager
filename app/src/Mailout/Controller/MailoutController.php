<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Controller;

use App\Mailout\Repository\MailoutRepository;
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
        private readonly MailoutRepository $mailoutRepository,
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    #[Route('/send-product/{productId}', name: 'api_mailout_send_product', methods: ['POST'])]
    public function sendProductToAllUsers(int $productId, Request $request): JsonResponse
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

        // Prepare mailout records
        $mailouts = array_map(
            fn($user) => [
                'chat_id' => $user->getChatId(),
                'product_id' => $productId,
            ],
            $activeUsers
        );

        // Bulk insert mailout records
        $created = $this->mailoutRepository->bulkInsertMailouts($botIdentifier, $mailouts);

        return new JsonResponse([
            'message' => 'Mailout records created successfully',
            'created' => $created,
            'total_users' => count($activeUsers),
        ]);
    }

    #[Route('/statistics', name: 'api_mailout_statistics', methods: ['GET'])]
    public function getStatistics(Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        $productId = $request->query->getInt('product_id', 0);

        if ($productId > 0) {
            // Get statistics for specific product
            $statistics = $this->mailoutRepository->getStatisticsByProduct($productId, $botIdentifier);
        } else {
            // Get statistics for all products
            $statistics = $this->mailoutRepository->getAllStatistics($botIdentifier);
        }

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
