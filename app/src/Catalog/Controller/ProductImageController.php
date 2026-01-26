<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

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
                'path' => $imagePath
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload product image', [
                'error' => $e->getMessage()
            ]);

            return $this->json([
                'error' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }
}

