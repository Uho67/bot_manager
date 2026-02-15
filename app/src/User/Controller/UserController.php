<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // Get bot identifier from request attributes (set by authenticator)
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $filters = [
            'status' => $request->query->get('status', ''),
            'created_at_from' => $request->query->get('created_at_from', ''),
            'created_at_to' => $request->query->get('created_at_to', ''),
            'updated_at_from' => $request->query->get('updated_at_from', ''),
            'updated_at_to' => $request->query->get('updated_at_to', ''),
        ];

        $users = $this->userRepository->findByBotIdentifierWithFilters($botIdentifier, $filters);

        return $this->json([
            'member' => array_map(function (User $user) {
                return [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'username' => $user->getUsername(),
                    'chat_id' => $user->getChatId(),
                    'status' => $user->getStatus(),
                    'created_at' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'updated_at' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
                ];
            }, $users),
        ]);
    }

    #[Route('/mass-update', name: 'api_users_mass_update', methods: ['POST'])]
    public function massUpdate(Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || !isset($data['users']) || !\is_array($data['users'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected "users" array.'], 400);
        }

        $result = $this->userRepository->bulkUpsertUsers($botIdentifier, $data['users']);

        return new JsonResponse([
            'message' => 'Users processed successfully',
            'created' => $result['created'],
            'updated' => $result['updated'],
            'errors' => $result['errors'],
        ]);
    }
}
