<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/catalog')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('/categories/{id}', name: 'api_catalog_category_get', methods: ['GET'])]
    public function getCategory(int $id, Request $request): JsonResponse
    {
        // Get bot identifier from request attributes (set by authenticator)
        $botIdentifier = $request->attributes->get('bot_identifier');

        if (!$botIdentifier) {
            return new JsonResponse(['error' => 'Bot identifier not found'], 401);
        }

        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], 404);
        }

        // Check if category belongs to this bot
        if ($category->getBotIdentifier() !== $botIdentifier) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        // Get child categories filtered by bot identifier
        $childCategories = array_filter(
            $category->getChildCategories()->toArray(),
            fn($child) => $child->getBotIdentifier() === $botIdentifier
        );

        // Get products filtered by bot identifier
        $products = array_filter(
            $category->getProducts()->toArray(),
            fn($product) => $product->getBotIdentifier() === $botIdentifier
        );

        return new JsonResponse([
            'id' => $category->getId(),
            'name' => $category->getName(),
            'bot_identifier' => $category->getBotIdentifier(),
            'child_categories' => array_map(function ($child) {
                return [
                    'id' => $child->getId(),
                    'name' => $child->getName(),
                ];
            }, $childCategories),
            'products' => array_map(function ($product) {
                return [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'image' => $product->getImage(),
                ];
            }, $products),
        ]);
    }
}

