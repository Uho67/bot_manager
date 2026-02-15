<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Controller;

use App\Mailout\Repository\MailoutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram/mailout')]
class MailoutTelegramController extends AbstractController
{
    public function __construct(
        private readonly MailoutRepository $mailoutRepository,
    ) {
    }

    #[Route('/products', name: 'telegram_mailout_products', methods: ['GET'])]
    public function getProductIds(Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $productIds = $this->mailoutRepository->getProductIdsByBotIdentifier($botIdentifier);

        return new JsonResponse([
            'product_ids' => $productIds,
        ]);
    }

    #[Route('/by-products', name: 'telegram_mailout_by_products', methods: ['GET'])]
    public function getMailoutsByProductIds(Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $productIdsParam = $request->query->get('product_ids', '');
        $limit = $request->query->getInt('limit', 100);

        if (empty($productIdsParam)) {
            return new JsonResponse(['error' => 'product_ids parameter is required'], 400);
        }

        // Parse product_ids - can be comma-separated string or array
        $productIds = is_array($productIdsParam) 
            ? $productIdsParam 
            : array_filter(array_map('intval', explode(',', $productIdsParam)));

        if (empty($productIds)) {
            return new JsonResponse(['error' => 'Invalid product_ids'], 400);
        }

        $mailouts = $this->mailoutRepository->findByProductIdsAndBotIdentifier(
            $productIds,
            $botIdentifier,
            $limit
        );

        return new JsonResponse([
            'mailouts' => array_map(function ($mailout) {
                return [
                    'id' => $mailout->getId(),
                    'chat_id' => $mailout->getChatId(),
                    'product_id' => $mailout->getProductId(),
                    'status' => $mailout->getStatus(),
                    'created_at' => $mailout->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'sent_at' => $mailout->getSentAt()?->format('Y-m-d H:i:s'),
                ];
            }, $mailouts),
        ]);
    }

    #[Route('/delete', name: 'telegram_mailout_delete', methods: ['POST'])]
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

        $deleted = $this->mailoutRepository->bulkDeleteMailouts($botIdentifier, $data['ids']);

        return new JsonResponse([
            'message' => 'Mailouts deleted successfully',
            'deleted' => $deleted,
        ]);
    }
}
