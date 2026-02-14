<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Template\Service;

use App\Button\Repository\ButtonRepository;
use App\Template\Entity\Template;

readonly class TemplateFormatterService
{
    public function __construct(
        private ButtonRepository $buttonRepository,
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
        $formattedLayout = [];

        foreach ($layout as $line) {
            $formattedLine = $this->formatLayoutLine($line, $botIdentifier);
            if (!empty($formattedLine)) {
                $formattedLayout[] = $formattedLine;
            }
        }

        return $formattedLayout;
    }

    /**
     * Format a single layout line (array of button IDs) to array of button details
     */
    private function formatLayoutLine(array $line, string $botIdentifier): array
    {
        $formattedLine = [];

        foreach ($line as $buttonId) {
            $button = $this->buttonRepository->findByIdAndBotIdentifier($buttonId, $botIdentifier);
            if ($button) {
                $formattedLine[] = [
                    'label' => $button->getLabel(),
                    'button_type' => $button->getButtonType(),
                    'value' => $button->getValue(),
                ];
            }
        }

        return $formattedLine;
    }
}
