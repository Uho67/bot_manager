<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\User\Controller;

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
