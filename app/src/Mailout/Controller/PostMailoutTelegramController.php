<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Controller;

use App\Mailout\Repository\PostMailoutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram/post-mailout')]
class PostMailoutTelegramController extends AbstractController
{
    public function __construct(
        private readonly PostMailoutRepository $postMailoutRepository,
    ) {
    }

    #[Route('/posts', name: 'telegram_post_mailout_posts', methods: ['GET'])]
    public function getPostIds(Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $postIds = $this->postMailoutRepository->getPostIdsByBotIdentifier($botIdentifier);

        return new JsonResponse([
            'post_ids' => $postIds,
        ]);
    }

    #[Route('/by-posts', name: 'telegram_post_mailout_by_posts', methods: ['GET'])]
    public function getMailoutsByPostIds(Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $postIdsParam = $request->query->get('post_ids', '');
        $limit = $request->query->getInt('limit', 100);

        if (empty($postIdsParam)) {
            return new JsonResponse(['error' => 'post_ids parameter is required'], 400);
        }

        // Parse post_ids - can be comma-separated string or array
        $postIds = is_array($postIdsParam)
            ? $postIdsParam
            : array_filter(array_map('intval', explode(',', $postIdsParam)));

        if (empty($postIds)) {
            return new JsonResponse(['error' => 'Invalid post_ids'], 400);
        }

        $mailouts = $this->postMailoutRepository->findByPostIdsAndBotIdentifier(
            $postIds,
            $botIdentifier,
            $limit
        );

        return new JsonResponse([
            'mailouts' => array_map(function ($mailout) {
                return [
                    'id' => $mailout->getId(),
                    'chat_id' => $mailout->getChatId(),
                    'post_id' => $mailout->getPostId(),
                    'status' => $mailout->getStatus(),
                    'created_at' => $mailout->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'sent_at' => $mailout->getSentAt()?->format('Y-m-d H:i:s'),
                ];
            }, $mailouts),
        ]);
    }

    #[Route('/delete', name: 'telegram_post_mailout_delete', methods: ['POST'])]
    public function deleteMailouts(Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || !isset($data['ids']) || !\is_array($data['ids'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected "ids" array.'], 400);
        }

        if (empty($data['ids'])) {
            return new JsonResponse(['error' => 'No mailout IDs provided.'], 400);
        }

        $deleted = $this->postMailoutRepository->bulkDeletePostMailouts($botIdentifier, $data['ids']);

        return new JsonResponse([
            'message' => 'Post mailouts deleted successfully',
            'deleted' => $deleted,
        ]);
    }
}
