<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Repository\ProductImageRepository;
use App\Catalog\Repository\ProductRepository;
use App\Template\Entity\Template;
use App\Template\Repository\TemplateRepository;
use App\Template\Service\TemplateFormatterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram/catalog')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductImageRepository $productImageRepository,
        private readonly TemplateRepository $templateRepository,
        private readonly TemplateFormatterService $templateFormatter,
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire('%app.base_path%')]
        private readonly ?string $basePath = '',
    ) {
    }

    #[Route('/products/{id}', name: 'telegram_catalog_product_get', methods: ['GET'])]
    public function getProduct(int $id, Request $request): JsonResponse
    {
        // Get bot identifier from request attributes (set by authenticator)
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $product = $this->productRepository->findByIdAndBotIdentifier($id, $botIdentifier);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $template = $this->getTemplateForProduct($botIdentifier);
        $formattedTemplate = $template ? $this->templateFormatter->formatTemplate($template, $botIdentifier) : null;

        $imagesTemplate = $this->getImagesTemplateForProduct($botIdentifier);
        $formattedImagesTemplate = $imagesTemplate ? $this->templateFormatter->formatTemplate($imagesTemplate, $botIdentifier) : null;

        $baseUrl = $request->getSchemeAndHttpHost().rtrim($this->basePath ?? '', '/');

        // Build additional images array
        $additionalImages = [];
        foreach ($product->getImages() as $productImage) {
            $additionalImages[] = [
                'id' => $productImage->getId(),
                'image' => $baseUrl.'/'.ltrim($productImage->getImage(), '/'),
                'image_file_id' => $productImage->getImageFileId(),
                'sort_order' => $productImage->getSortOrder(),
            ];
        }

        return new JsonResponse([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'image' => $baseUrl.'/'.ltrim($product->getImage(), '/'),
            'image_file_id' => $product->getImageFileId(),
            'enabled' => $product->isEnabled(),
            'additional_images' => $additionalImages,
            'template' => $formattedTemplate,
            'images_template' => $formattedImagesTemplate,
        ]);
    }

    #[Route('/products/{id}/image-file-id', name: 'telegram_catalog_product_update_image_file_id', methods: ['PATCH'])]
    public function updateImageFileId(int $id, Request $request): JsonResponse
    {
        // Get bot identifier from request attributes (set by authenticator)
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $product = $this->productRepository->findByIdAndBotIdentifier($id, $botIdentifier);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['image_file_id'])) {
            return new JsonResponse(['error' => 'image_file_id is required'], 400);
        }

        $product->setImageFileId($data['image_file_id']);
        $this->productRepository->save($product, true);

        return new JsonResponse([
            'id' => $product->getId(),
            'image_file_id' => $product->getImageFileId(),
            'message' => 'Image file ID updated successfully',
        ]);
    }

    #[Route('/product-images/{imageId}/image-file-id', name: 'telegram_catalog_product_image_update_file_id', methods: ['PATCH'])]
    public function updateAdditionalImageFileId(int $imageId, Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';

        $productImage = $this->productImageRepository->find($imageId);

        if (!$productImage) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $product = $productImage->getProduct();
        if (!$product || $product->getBotIdentifier() !== $botIdentifier) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['image_file_id'])) {
            return new JsonResponse(['error' => 'image_file_id is required'], 400);
        }

        $productImage->setImageFileId($data['image_file_id']);
        $this->productImageRepository->save($productImage, true);

        return new JsonResponse([
            'id' => $productImage->getId(),
            'image_file_id' => $productImage->getImageFileId(),
            'message' => 'Image file ID updated successfully',
        ]);
    }

    private function getImagesTemplateForProduct(string $botIdentifier): ?Template
    {
        $templates = $this->templateRepository->findByTypeAndBotIdentifier(
            Template::TYPE_IMAGES,
            $botIdentifier
        );

        return $templates[0] ?? null;
    }

    private function getTemplateForProduct(string $botIdentifier): ?Template
    {
        $templates = $this->templateRepository->findByTypeAndBotIdentifier(
            'product',
            $botIdentifier
        );

        return $templates[0] ?? null;
    }
}
