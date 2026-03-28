<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Controller;

use App\Bot\Entity\Bot;
use App\Config\Service\ConfigService;
use App\Mailout\Entity\PostMailout;
use App\Mailout\Repository\PostMailoutRepository;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/mailout')]
class MailoutController extends AbstractController
{
    public function __construct(
        private readonly PostMailoutRepository $postMailoutRepository,
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly HttpClientInterface $httpClient,
        private readonly ConfigService $configService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/send-post/{postId}', name: 'api_mailout_send_post', methods: ['POST'])]
    public function sendPostToAllUsers(int $postId, Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        $body = json_decode($request->getContent(), true) ?? [];
        $removeMode = ($body['remove_mode'] ?? '') === PostMailout::REMOVE_MODE_NOT_REMOVE
            ? PostMailout::REMOVE_MODE_NOT_REMOVE
            : PostMailout::REMOVE_MODE_REMOVE;

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
                'remove_mode' => $removeMode,
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

    #[Route('/clean/{postId}', name: 'api_mailout_clean', methods: ['DELETE'])]
    public function cleanByPostId(int $postId): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        $deleted = $this->postMailoutRepository->deleteAllByPostAndBotIdentifier($postId, $botIdentifier);

        return new JsonResponse([
            'message' => 'Mailouts cleaned successfully',
            'deleted' => $deleted,
        ]);
    }

    #[Route('/sent-stats', name: 'api_mailout_sent_stats', methods: ['GET'])]
    public function getSentStats(): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        [$endpoint, $bot] = $this->resolveNestJsEndpointAndBot($botIdentifier);
        if ($endpoint instanceof JsonResponse) {
            return $endpoint;
        }

        try {
            $response = $this->httpClient->request('GET', $endpoint . '/telegram/sent-message/stats', [
                'headers' => ['Authorization' => \sprintf('Bearer %s', $bot->getApiKey())],
            ]);

            return new JsonResponse(
                json_decode($response->getContent(false), true),
                $response->getStatusCode(),
            );
        } catch (ExceptionInterface $e) {
            return new JsonResponse(['error' => 'Failed to connect to NestJS', 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/delete-sent/{postId}', name: 'api_mailout_delete_sent', methods: ['POST'])]
    public function deleteSent(int $postId): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        [$endpoint, $bot] = $this->resolveNestJsEndpointAndBot($botIdentifier);
        if ($endpoint instanceof JsonResponse) {
            return $endpoint;
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                $endpoint . '/telegram/sent-message/mark-delete/' . $postId,
                ['headers' => ['Authorization' => \sprintf('Bearer %s', $bot->getApiKey())]],
            );

            return new JsonResponse(
                json_decode($response->getContent(false), true),
                $response->getStatusCode(),
            );
        } catch (ExceptionInterface $e) {
            return new JsonResponse(['error' => 'Failed to connect to NestJS', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Resolves NestJS base endpoint and Bot entity for the given bot identifier.
     * Returns [string $endpoint, Bot $bot] on success, or [JsonResponse $error, null] on failure.
     *
     * @return array{0: string|JsonResponse, 1: Bot|null}
     */
    private function resolveNestJsEndpointAndBot(string $botIdentifier): array
    {
        $endpoint = $this->configService->getBotUrl($botIdentifier);

        if (empty($endpoint)) {
            return [new JsonResponse([
                'error' => 'Bot URL not configured',
                'message' => 'Please configure the endpoint at path: bot.url',
            ], 400), null];
        }

        $bot = $this->entityManager->getRepository(Bot::class)->findOneBy(['bot_identifier' => $botIdentifier]);

        if (!$bot) {
            return [new JsonResponse(['error' => 'Bot not found'], 404), null];
        }

        if (!$bot->getApiKey()) {
            return [new JsonResponse(['error' => 'API key not found for bot'], 400), null];
        }

        return [$endpoint, $bot];
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
