<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Template\Service;

use App\Button\Repository\ButtonRepository;
use App\Catalog\Constants\ButtonConstants;
use App\Catalog\Entity\Category;
use App\Catalog\Entity\Product;
use App\Catalog\Repository\CategoryRepository;
use App\Catalog\Repository\ProductRepository;
use App\Template\Entity\Template;

readonly class TemplateFormatterService
{
    public function __construct(
        private ButtonRepository $buttonRepository,
        private CategoryRepository $categoryRepository,
        private ProductRepository $productRepository,
    ) {
    }

    /**
     * Format a Template entity to array representation with formatted layout
     */
    public function formatTemplate(Template $template, string $botIdentifier): array
    {
        return [
            'id' => $template->getId(),
            'name' => $template->getName(),
            'type' => $template->getType(),
            'layout' => $this->formatLayout($template->getLayout(), $botIdentifier),
        ];
    }

    /**
     * Format template layout array with button details
     */
    public function formatLayout(array $layout, string $botIdentifier): array
    {
        if (empty($layout)) {
            return [];
        }

        $buttonMap = array_merge(
            $this->getRegularButtons($botIdentifier),
            $this->getCategoryButtons($botIdentifier),
            $this->getProductButtons($botIdentifier)
        );

        // Add backward compatibility: also map numeric IDs (both int and string) for old templates
        $buttons = $this->buttonRepository->findAllByBotIdentifier($botIdentifier);
        foreach ($buttons as $button) {
            $buttonId = $button->getId();
            $formattedButton = $this->formatRegularButton($button);
            // Add both integer and string keys for backward compatibility
            if (!isset($buttonMap[$buttonId])) {
                $buttonMap[$buttonId] = $formattedButton;
            }
            $numericIdString = (string) $buttonId;
            if (!isset($buttonMap[$numericIdString])) {
                $buttonMap[$numericIdString] = $formattedButton;
            }
        }

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

    private function getCategoryButtons(string $botIdentifier): array
    {
        $categories = $this->categoryRepository->findAllByBotIdentifier($botIdentifier);

        return array_reduce(
            $categories,
            fn (array $carry, Category $category) => array_merge($carry, [
                ButtonConstants::PREFIX_CATEGORY.$category->getId() => $this->formatCategoryButton($category),
            ]),
            []
        );
    }

    private function getProductButtons(string $botIdentifier): array
    {
        $products = $this->productRepository->findAllByBotIdentifier($botIdentifier);

        return array_reduce(
            $products,
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
