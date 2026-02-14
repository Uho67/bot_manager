<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Repository\CategoryRepository;
use App\Catalog\Service\CategoryButtonFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram/catalog')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CategoryButtonFormatter $buttonFormatter,
    ) {
    }

    #[Route(
        '/categories/{id}/image-file-id',
        name: 'telegram_catalog_category_update_image_file_id',
        methods: ['PATCH']
    )]
    public function updateImageFileId(int $id, Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $category = $this->categoryRepository->findByIdAndBotIdentifier($id, $botIdentifier);

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['image_file_id'])) {
            return new JsonResponse(['error' => 'image_file_id is required'], 400);
        }

        $category->setImageFileId($data['image_file_id']);
        $this->categoryRepository->save($category, true);

        return new JsonResponse([
            'id' => $category->getId(),
            'image_file_id' => $category->getImageFileId(),
            'message' => 'Image file ID updated successfully',
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
            ];
        }, $categories));
    }

    #[Route('/categories/{id}', name: 'telegram_catalog_category_get', methods: ['GET'])]
    public function getCategoryWithButtons(int $id, Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $category = $this->categoryRepository->findByIdAndBotIdentifier($id, $botIdentifier);

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], 404);
        }

        $formattedData = $this->buttonFormatter->format($category, $request, $botIdentifier);

        return new JsonResponse($formattedData);
    }
}
