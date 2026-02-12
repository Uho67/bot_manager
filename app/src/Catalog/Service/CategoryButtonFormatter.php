<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Service;

use App\Button\Repository\ButtonRepository;
use App\Catalog\Constants\ButtonConstants;
use App\Catalog\Entity\Category;
use App\Catalog\Entity\Product;
use Symfony\Component\HttpFoundation\Request;

readonly class CategoryButtonFormatter
{
    public function __construct(
        private ButtonRepository $buttonRepository,
    ) {
    }

    public function format(Category $category, Request $request, string $botIdentifier): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'is_root' => $category->isRoot(),
            'image' => $this->buildImageUrl($category, $request),
            'image_file_id' => $category->getImageFileId(),
            'layout' => $this->buildLayout($category, $botIdentifier),
        ];
    }

    private function buildImageUrl(Category $category, Request $request): string
    {
        $imageUrl = $category->getImage() ?? '';

        return $request->getSchemeAndHttpHost().'/'.ltrim($imageUrl, '/');
    }

    private function buildLayout(Category $category, string $botIdentifier): array
    {
        $layout = $category->getLayout();

        if (empty($layout)) {
            return [];
        }

        $buttonMap = array_merge(
            $this->getRegularButtons($botIdentifier),
            $this->getChildCategoryButtons($category),
            $this->getProductButtons($category)
        );

        return $this->transformLayoutToButtons($layout, $buttonMap);
    }

    private function getRegularButtons(string $botIdentifier): array
    {
        $buttons = $this->buttonRepository->findAllByBotIdentifier($botIdentifier);

        return array_reduce(
            $buttons,
            fn (array $carry, $button) => array_merge($carry, [
                ButtonConstants::PREFIX_BUTTON . $button->getId() => $this->formatRegularButton($button),
            ]),
            []
        );
    }

    private function getChildCategoryButtons(Category $category): array
    {
        return array_reduce(
            $category->getChildCategories()->toArray(),
            fn (array $carry, Category $child) => array_merge($carry, [
                ButtonConstants::PREFIX_CATEGORY.$child->getId() => $this->formatCategoryButton($child),
            ]),
            []
        );
    }

    private function getProductButtons(Category $category): array
    {
        return array_reduce(
            $category->getProducts()->toArray(),
            fn (array $carry, Product $product) => array_merge($carry, [
                ButtonConstants::PREFIX_PRODUCT.$product->getId() => $this->formatProductButton($product),
            ]),
            []
        );
    }

    private function transformLayoutToButtons(array $layout, array $buttonMap): array
    {
        $result = [];

        foreach ($layout as $line) {
            $formattedLine = $this->transformLineToButtons($line, $buttonMap);
            if (!empty($formattedLine)) {
                $result[] = $formattedLine;
            }
        }

        return $result;
    }

    private function transformLineToButtons(array $line, array $buttonMap): array
    {
        return array_values(
            array_filter(
                array_map(
                    fn ($buttonId) => $buttonMap[$buttonId] ?? null,
                    $line
                )
            )
        );
    }

    private function formatRegularButton($button): array
    {
        return [
            'id' => $button->getId(),
            'label' => $button->getLabel(),
            'button_type' => $button->getButtonType(),
            'value' => $button->getValue(),
        ];
    }

    private function formatCategoryButton(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'label' => $category->getName(),
            'button_type' => ButtonConstants::TYPE_CALLBACK,
            'value' => sprintf(ButtonConstants::VALUE_FORMAT_CATEGORY, $category->getId()),
        ];
    }

    private function formatProductButton(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'label' => $product->getName(),
            'button_type' => ButtonConstants::TYPE_CALLBACK,
            'value' => sprintf(ButtonConstants::VALUE_FORMAT_PRODUCT, $product->getId()),
        ];
    }
}
