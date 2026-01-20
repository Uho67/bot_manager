<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\AdminUser\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AuthController
{
    private $userProvider;

    public function __construct(
        #[Autowire(service: 'security.user.provider.concrete.admin_user_provider')]
        $userProvider
    ) {
        $this->userProvider = $userProvider;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $username = $data['admin_name'] ?? null;
        $password = $data['admin_password'] ?? null;

        if (!$username || !$password) {
            return new JsonResponse(['error' => 'Username and password required'], 400);
        }

        try {
            $user = $this->userProvider->loadUserByIdentifier($username);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'User not found',
                'debug' => $e->getMessage()
            ], 401);
        }

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 401);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'error' => 'Invalid password',
                'debug' => 'Password validation failed'
            ], 401);
        }

        $token = $jwtManager->create($user);
        return new JsonResponse([
            'token' => $token,
            'admin_name' => $user->getAdminName(),
            'bot_code' => $user->getBotCode(),
            'roles' => $user->getRoles(),
        ]);
    }
}
