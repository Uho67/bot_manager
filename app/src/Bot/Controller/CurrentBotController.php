<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Bot\Controller;

use App\Bot\Entity\Bot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

readonly class CurrentBotController
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/my-bot', name: 'api_update_current_bot', methods: ['PATCH'])]
    public function update(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        if (!$user || !is_object($user) || !method_exists($user, 'getBotIdentifier')) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }
        $botIdentifier = $user->getBotIdentifier();
        $bot = $this->entityManager->getRepository(Bot::class)->findOneBy(['bot_identifier' => $botIdentifier]);
        if (!$bot) {
            return new JsonResponse(['error' => 'Bot not found'], 404);
        }
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid request body'], 400);
        }
        if (array_key_exists('bot_code', $data)) {
            $bot->setBotCode($data['bot_code']);
        }
        if (array_key_exists('api_key', $data)) {
            $bot->setApiKey($data['api_key']);
        }
        $this->entityManager->persist($bot);
        $this->entityManager->flush();
        $responseData = $this->serializer->normalize($bot, null, ['groups' => ['bot:read']]);
        return new JsonResponse($responseData);
    }
}

