<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\User\Controller;

use App\Mailout\Repository\PostMailoutRepository;
use App\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/users')]
class UserAdminController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PostMailoutRepository $postMailoutRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    #[Route('/mass-delete', name: 'api_users_mass_delete', methods: ['POST'])]
    public function massDelete(Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || !isset($data['ids']) || !\is_array($data['ids'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected "ids" array.'], 400);
        }

        if (empty($data['ids'])) {
            return new JsonResponse(['error' => 'No user IDs provided.'], 400);
        }

        $deleted = $this->userRepository->bulkDeleteUsers($botIdentifier, $data['ids']);

        return new JsonResponse([
            'message' => 'Users deleted successfully',
            'deleted' => $deleted,
        ]);
    }

    #[Route('/mass-send-post', name: 'api_users_mass_send_post', methods: ['POST'])]
    public function massSendPost(Request $request): JsonResponse
    {
        $adminUser = $this->getUserFromAuth();
        $botIdentifier = $adminUser->getBotIdentifier();

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || !isset($data['ids']) || !\is_array($data['ids'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected "ids" array.'], 400);
        }

        if (empty($data['ids'])) {
            return new JsonResponse(['error' => 'No user IDs provided.'], 400);
        }

        if (!isset($data['post_id']) || !\is_int($data['post_id'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected integer "post_id".'], 400);
        }

        $users = $this->userRepository->findByIdsAndBotIdentifier($data['ids'], $botIdentifier);

        if (empty($users)) {
            return new JsonResponse(['message' => 'No matching users found', 'created' => 0]);
        }

        $mailouts = array_map(
            fn($user) => ['chat_id' => $user->getChatId(), 'post_id' => $data['post_id']],
            $users
        );

        $created = $this->postMailoutRepository->bulkInsertPostMailouts($botIdentifier, $mailouts);

        return new JsonResponse([
            'message' => 'Post mailout records created successfully',
            'created' => $created,
        ]);
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
