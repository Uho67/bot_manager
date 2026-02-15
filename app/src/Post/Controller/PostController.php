<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Post\Controller;

use App\Catalog\Repository\ProductRepository;
use App\Post\Entity\Post;
use App\Post\Repository\PostRepository;
use App\Template\Entity\Template;
use App\Template\Repository\TemplateRepository;
use App\Template\Service\TemplateFormatterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly TemplateRepository $templateRepository,
        private readonly TemplateFormatterService $templateFormatter,
        private readonly ProductRepository $productRepository,
    ) {
    }

    #[Route('/post/start', name: 'telegram_post_get_start', methods: ['GET'])]
    public function getStartPost(Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $post = $this->postRepository->findByTemplateTypeAndBotIdentifier('start', $botIdentifier);

        if (!$post) {
            return new JsonResponse(['error' => 'Start post not found'], 404);
        }

        return $this->formatPostResponse($post, $botIdentifier, $request);
    }

    #[Route('/post/{id}', name: 'telegram_post_get_by_id', methods: ['GET'])]
    public function getPostById(int $id, Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $post = $this->postRepository->findEnabledByIdAndBotIdentifier($id, $botIdentifier);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        return $this->formatPostResponse($post, $botIdentifier, $request);
    }

    #[Route('/post/{id}/image-file-id', name: 'telegram_post_update_image_file_id', methods: ['PATCH'])]
    public function updateImageFileId(int $id, Request $request): JsonResponse
    {
        // Get bot identifier from request attributes (set by authenticator)
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $post = $this->postRepository->findByIdAndBotIdentifier($id, $botIdentifier);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['image_file_id'])) {
            return new JsonResponse(['error' => 'image_file_id is required'], 400);
        }

        $post->setImageFileId($data['image_file_id']);
        $this->postRepository->save($post, true);

        return new JsonResponse([
            'id' => $post->getId(),
            'image_file_id' => $post->getImageFileId(),
            'message' => 'Image file ID updated successfully',
        ]);
    }

    #[Route('/post/product/{id}', name: 'telegram_post_get_product', methods: ['GET'])]
    public function getProductWithPostLayout(int $id, Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        // Get product
        $product = $this->productRepository->findByIdAndBotIdentifier($id, $botIdentifier);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $template = $this->templateRepository->findFirstByTypeAndBotIdentifier('post', $botIdentifier);
        $formattedTemplate = $template ? $this->templateFormatter->formatTemplate($template, $botIdentifier) : null;

        return new JsonResponse([
            'post' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'image' => $this->getImageUrl($product->getImage(), $request),
                'image_file_id' => $product->getImageFileId(),
                'template_type' => $template->getType(),
                'template' => $formattedTemplate,
            ],
        ]);
    }

    private function formatPostResponse(Post $post, string $botIdentifier, Request $request): JsonResponse
    {
        $template = $this->getTemplateForPost($post, $botIdentifier);
        $formattedTemplate = $template ? $this->templateFormatter->formatTemplate($template, $botIdentifier) : null;

        return new JsonResponse([
            'id' => $post->getId(),
            'name' => $post->getName(),
            'description' => $post->getDescription(),
            'image' => $this->getImageUrl($post->getImage(), $request),
            'image_file_id' => $post->getImageFileId(),
            'template_type' => $post->getTemplateType(),
            'template' => $formattedTemplate,
        ]);
    }

    private function getTemplateForPost(Post $post, string $botIdentifier): ?Template
    {
        $templates = $this->templateRepository->findByTypeAndBotIdentifier(
            $post->getTemplateType(),
            $botIdentifier
        );

        return $templates[0] ?? null;
    }

    private function getImageUrl(?string $imagePath, Request $request): ?string
    {
        if (!$imagePath) {
            return null;
        }

        return $request->getSchemeAndHttpHost() . '/' . \ltrim($imagePath, '/');
    }
}
