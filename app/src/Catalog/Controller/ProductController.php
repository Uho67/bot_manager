<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/catalog')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    #[Route('/products/{id}', name: 'api_catalog_product_get', methods: ['GET'])]
    public function getProduct(int $id, Request $request): JsonResponse
    {
        // Get bot identifier from request attributes (set by authenticator)
        $botIdentifier = $request->attributes->get('bot_identifier');

        if (!$botIdentifier) {
            return new JsonResponse(['error' => 'Bot identifier not found'], 401);
        }

        $product = $this->productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        // Check if product belongs to this bot
        if ($product->getBotIdentifier() !== $botIdentifier) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        return new JsonResponse([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'image' => $product->getImage(),
            'bot_identifier' => $product->getBotIdentifier(),
            'categories' => array_map(function ($category) {
                return [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ];
            }, $product->getCategories()->toArray()),
        ]);
    }
}

