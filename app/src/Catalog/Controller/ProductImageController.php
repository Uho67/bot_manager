<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Entity\ProductImage;
use App\Catalog\Repository\ProductImageRepository;
use App\Catalog\Repository\ProductRepository;
use App\Catalog\Service\ImageService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ProductImageController extends AbstractController
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly ProductRepository $productRepository,
        private readonly ProductImageRepository $productImageRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/product/upload-image', name: 'product_upload_image', methods: ['POST'])]
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
            $imagePath = $this->imageService->uploadImage($file, $botIdentifier, 'product');

            return $this->json([
                'success' => true,
                'path' => $imagePath,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload product image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to upload image: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/product/{id}/additional-images', name: 'product_upload_additional_image', methods: ['POST'])]
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
            $product = $this->productRepository->findByIdAndBotIdentifier($id, $botIdentifier);

            if (!$product) {
                return $this->json(['error' => 'Product not found'], 404);
            }

            // Upload image file
            $imagePath = $this->imageService->uploadImage($file, $botIdentifier, 'product');

            // Calculate next sort_order
            $maxSortOrder = 0;
            foreach ($product->getImages() as $existingImage) {
                if ($existingImage->getSortOrder() > $maxSortOrder) {
                    $maxSortOrder = $existingImage->getSortOrder();
                }
            }

            // Create ProductImage entity
            $productImage = new ProductImage();
            $productImage->setImage($imagePath);
            $productImage->setSortOrder($maxSortOrder + 1);
            $productImage->setProduct($product);

            $this->productImageRepository->save($productImage, true);

            return $this->json([
                'success' => true,
                'id' => $productImage->getId(),
                'path' => $imagePath,
                'sort_order' => $productImage->getSortOrder(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload additional product image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to upload image: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/product/additional-image/{id}', name: 'product_delete_additional_image', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAdditionalImage(int $id): JsonResponse
    {
        try {
            $productImage = $this->productImageRepository->find($id);

            if (!$productImage) {
                return $this->json(['error' => 'Image not found'], 404);
            }

            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getBotIdentifier')) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }

            $botIdentifier = $user->getBotIdentifier();
            $product = $productImage->getProduct();

            if (!$product || $product->getBotIdentifier() !== $botIdentifier) {
                return $this->json(['error' => 'Image not found'], 404);
            }

            // Delete the file from disk
            $this->imageService->deleteImage($productImage->getImage());

            // Remove entity
            $this->productImageRepository->remove($productImage, true);

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete additional product image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to delete image: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/product/{id}/remove-image', name: 'product_remove_image', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeImage(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getBotIdentifier')) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }

            $botIdentifier = $user->getBotIdentifier();
            $product = $this->productRepository->findByIdAndBotIdentifier($id, $botIdentifier);

            if (!$product) {
                return $this->json(['error' => 'Product not found'], 404);
            }

            if ($product->getImage()) {
                $this->imageService->deleteImage($product->getImage());
            }

            $product->setImage(null);
            $product->setImageFileId(null);
            $this->productRepository->getEntityManager()->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove product image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to remove image: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/product/additional-images/reorder', name: 'product_reorder_additional_images', methods: ['PATCH'])]
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

                $productImage = $this->productImageRepository->find($imageData['id']);

                if (!$productImage) {
                    continue;
                }

                $product = $productImage->getProduct();
                if (!$product || $product->getBotIdentifier() !== $botIdentifier) {
                    continue;
                }

                $productImage->setSortOrder((int) $imageData['sort_order']);
                $this->productImageRepository->save($productImage);
            }

            $this->productImageRepository->getEntityManager()->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to reorder additional product images', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to reorder images: '.$e->getMessage(),
            ], 500);
        }
    }
}
