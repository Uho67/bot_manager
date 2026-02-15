<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Controller;

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
        private readonly TemplateRepository $templateRepository,
        private readonly TemplateFormatterService $templateFormatter,
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

        return new JsonResponse([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'image' => $request->getSchemeAndHttpHost().'/'.\ltrim($product->getImage(), '/'),
            'image_file_id' => $product->getImageFileId(),
            'template' => $formattedTemplate,
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

    private function getTemplateForProduct(string $botIdentifier): ?Template
    {
        $templates = $this->templateRepository->findByTypeAndBotIdentifier(
            'product',
            $botIdentifier
        );

        return $templates[0] ?? null;
    }
}
