<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\AdminUser\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CurrentAdminController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/me', name: 'api_current_admin', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !is_object($user)) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        // Serialize with the admin_user:read group
        $data = $this->serializer->normalize($user, null, ['groups' => ['admin_user:read']]);
        return new JsonResponse($data);
    }

    #[Route('/api/me', name: 'api_update_current_admin', methods: ['PATCH'])]
    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !is_object($user)) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid request body'], 400);
        }

        // Only allow updating certain fields
        if (isset($data['admin_password']) && $data['admin_password']) {
            $user->setAdminPassword(
                $passwordHasher->hashPassword($user, $data['admin_password'])
            );
        }
        if (array_key_exists('bot_code', $data)) {
            $user->setBotCode($data['bot_code']);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $responseData = $this->serializer->normalize($user, null, ['groups' => ['admin_user:read']]);
        return new JsonResponse($responseData);
    }
}
