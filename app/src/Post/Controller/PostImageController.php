<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Post\Controller;

use App\Catalog\Service\ImageService;
use App\Post\Repository\PostRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class PostImageController extends AbstractController
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly PostRepository $postRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/post/upload-image', name: 'post_upload_image', methods: ['POST'])]
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
            $imagePath = $this->imageService->uploadImage($file, $botIdentifier, 'post');

            return $this->json([
                'success' => true,
                'path' => $imagePath,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload post image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to upload image: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/post/{id}/remove-image', name: 'post_remove_image', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeImage(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getBotIdentifier')) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }

            $botIdentifier = $user->getBotIdentifier();
            $post = $this->postRepository->findByIdAndBotIdentifier($id, $botIdentifier);

            if (!$post) {
                return $this->json(['error' => 'Post not found'], 404);
            }

            if ($post->getImage()) {
                $this->imageService->deleteImage($post->getImage());
            }

            $post->setImage(null);
            $post->setImageFileId(null);
            $this->postRepository->getEntityManager()->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove post image', [
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Failed to remove image: ' . $e->getMessage(),
            ], 500);
        }
    }
}
