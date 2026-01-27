<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Controller;

use App\Config\Service\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/cache')]
class CacheClearController extends AbstractController
{
    public function __construct(
        private readonly ConfigService $configService,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    #[Route('/clear-config', name: 'api_cache_clear_config', methods: ['POST'])]
    public function clearConfigCache(): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !\is_object($user) || !method_exists($user, 'getBotIdentifier')) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $botIdentifier = $user->getBotIdentifier();
        $this->configService->clearCache($botIdentifier);

        return $this->json(['message' => 'Config cache cleared successfully for bot: ' . $botIdentifier]);
    }
}
