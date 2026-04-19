<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Entity\CategoryImage;
use App\Catalog\Repository\CategoryImageRepository;
use App\Catalog\Repository\CategoryRepository;
use App\Catalog\Service\ImageService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class CategoryImageController extends AbstractController
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly CategoryRepository $categoryRepository,
        private readonly CategoryImageRepository $categoryImageRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/category/upload-image', name: 'category_upload_image', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            /** @var UploadedFile|null $file */
            $file = $request->files->get('image');

            if (!$file) {
                return $this->json(['error' => 'No file uploaded'], 400);
            }

            // Get current user's bot_identifier
            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getBotIdentifier')) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }

            $botIdentifier = $user->getBotIdentifier();

            // Upload image
            $imagePath = $this->imageService->uploadImage($file, $botIdentifier, 'category');

            return $this->json([
                'success' => true,
                'path' => $imagePath,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload category image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to upload image: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/category/{id}/additional-images', name: 'category_upload_additional_image', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function uploadAdditionalImage(int $id, Request $request): JsonResponse
    {
        try {
            /** @var UploadedFile|null $file */
            $file = $request->files->get('image');

            if (!$file) {
                return $this->json(['error' => 'No file uploaded'], 400);
            }

            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getBotIdentifier')) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }

            $botIdentifier = $user->getBotIdentifier();
            $category = $this->categoryRepository->findByIdAndBotIdentifier($id, $botIdentifier);

            if (!$category) {
                return $this->json(['error' => 'Category not found'], 404);
            }

            // Upload image file
            $imagePath = $this->imageService->uploadImage($file, $botIdentifier, 'category');

            // Calculate next sort_order
            $maxSortOrder = 0;
            foreach ($category->getImages() as $existingImage) {
                if ($existingImage->getSortOrder() > $maxSortOrder) {
                    $maxSortOrder = $existingImage->getSortOrder();
                }
            }

            // Create CategoryImage entity
            $categoryImage = new CategoryImage();
            $categoryImage->setImage($imagePath);
            $categoryImage->setSortOrder($maxSortOrder + 1);
            $categoryImage->setCategory($category);

            $this->categoryImageRepository->save($categoryImage, true);

            return $this->json([
                'success' => true,
                'id' => $categoryImage->getId(),
                'path' => $imagePath,
                'sort_order' => $categoryImage->getSortOrder(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload additional category image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to upload image: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/category/additional-image/{id}', name: 'category_delete_additional_image', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAdditionalImage(int $id): JsonResponse
    {
        try {
            $categoryImage = $this->categoryImageRepository->find($id);

            if (!$categoryImage) {
                return $this->json(['error' => 'Image not found'], 404);
            }

            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getBotIdentifier')) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }

            $botIdentifier = $user->getBotIdentifier();
            $category = $categoryImage->getCategory();

            if (!$category || $category->getBotIdentifier() !== $botIdentifier) {
                return $this->json(['error' => 'Image not found'], 404);
            }

            // Delete the file from disk
            $this->imageService->deleteImage($categoryImage->getImage());

            // Remove entity
            $this->categoryImageRepository->remove($categoryImage, true);

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete additional category image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to delete image: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/category/additional-images/reorder', name: 'category_reorder_additional_images', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function reorderAdditionalImages(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['images']) || !is_array($data['images'])) {
                return $this->json(['error' => 'Invalid request body'], 400);
            }

            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getBotIdentifier')) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }

            $botIdentifier = $user->getBotIdentifier();

            foreach ($data['images'] as $imageData) {
                if (!isset($imageData['id'], $imageData['sort_order'])) {
                    continue;
                }

                $categoryImage = $this->categoryImageRepository->find($imageData['id']);

                if (!$categoryImage) {
                    continue;
                }

                $category = $categoryImage->getCategory();
                if (!$category || $category->getBotIdentifier() !== $botIdentifier) {
                    continue;
                }

                $categoryImage->setSortOrder((int) $imageData['sort_order']);
                $this->categoryImageRepository->save($categoryImage);
            }

            $this->categoryImageRepository->getEntityManager()->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to reorder additional category images', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to reorder images: '.$e->getMessage(),
            ], 500);
        }
    }
}
