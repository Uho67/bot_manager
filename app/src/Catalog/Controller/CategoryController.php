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

#[Route('/telegram/catalog')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('/categories/{id}', name: 'telegram_catalog_category_get', methods: ['GET'])]
    public function getCategory(int $id, Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $category = $this->categoryRepository->findByIdAndBotIdentifier($id, $botIdentifier);
        if (empty($category)) {
            return new JsonResponse(['error' => 'Category not found'], 404);
        }

        return new JsonResponse([
            'id' => $category->getId(),
            'name' => $category->getName(),
            'sort_order' => $category->getSortOrder(),
            'child_categories' => array_map(function ($child) {
                return [
                    'id' => $child->getId(),
                    'name' => $child->getName(),
                    'sort_order' => $child->getSortOrder(),
                ];
            }, $category->getChildCategories()->toArray()),
            'products' => array_map(function ($product) {
                return [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'sort_order' => $product->getSortOrder(),
                ];
            }, $category->getProducts()->toArray()),
        ]);
    }

    #[Route('/categories', name: 'telegram_catalog_category_list_get', methods: ['GET'])]
    public function getCategoryList(Request $request): JsonResponse
    {
        // Get bot identifier from request attributes (set by authenticator)
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $categories = $this->categoryRepository->findAllByBotIdentifier($botIdentifier);
        if (empty($categories)) {
            return new JsonResponse(['error' => 'Any category is assigned to the bot'], 404);
        }

        return new JsonResponse(array_map(function ($category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'sort_order' => $category->getSortOrder(),
            ];
        }, $categories));
    }
}
