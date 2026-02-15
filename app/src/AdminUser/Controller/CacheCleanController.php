<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\AdminUser\Controller;

use App\Bot\Entity\Bot;
use App\Config\Service\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class CacheCleanController
{
    private const string CONFIG_PATH = 'telegram.cache.clean.endpoint';

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private EntityManagerInterface $entityManager,
        private ConfigService $configService,
        private HttpClientInterface $httpClient,
    ) {
    }

    #[Route('/api/admin-user/cache-clean', name: 'api_admin_user_cache_clean', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !\is_object($user) || !method_exists($user, 'getBotIdentifier')) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $botIdentifier = $user->getBotIdentifier();

        if (!$botIdentifier) {
            return new JsonResponse(['error' => 'Bot identifier not found for user'], 400);
        }

        $endpoint = $this->configService->get($botIdentifier, self::CONFIG_PATH, '');

        if (empty($endpoint)) {
            return new JsonResponse([
                'error' => 'Cache clean endpoint not configured',
                'message' => \sprintf('Please configure the endpoint at path: %s', self::CONFIG_PATH),
            ], 400);
        }

        // Get bot to retrieve hashed API key
        $bot = $this->entityManager->getRepository(Bot::class)->findOneBy(
            ['bot_identifier' => $botIdentifier]
        );

        if (!$bot) {
            return new JsonResponse(['error' => 'Bot not found'], 404);
        }

        $apiKey = $bot->getApiKey();

        if (!$apiKey) {
            return new JsonResponse(['error' => 'API key not found for bot'], 400);
        }

        try {
            // Send DELETE request with Bearer authorization using hashed API key
            $response = $this->httpClient->request('DELETE', $endpoint, [
                'headers' => [
                    'Authorization' => \sprintf('Bearer %s', $apiKey),
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if ($statusCode >= 200 && $statusCode < 300) {
                return new JsonResponse([
                    'message' => 'Cache cleaned successfully',
                    'status_code' => $statusCode,
                    'response' => $content ? json_decode($content, true) : null,
                ]);
            }

            return new JsonResponse([
                'error' => 'Failed to clean cache',
                'status_code' => $statusCode,
                'response' => $content ? json_decode($content, true) : null,
            ], $statusCode);
        } catch (ExceptionInterface $e) {
            return new JsonResponse([
                'error' => 'Failed to connect to cache clean endpoint',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
