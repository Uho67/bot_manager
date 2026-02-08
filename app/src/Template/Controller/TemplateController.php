<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Template\Controller;

use App\Button\Repository\ButtonRepository;
use App\Template\Repository\TemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram')]
class TemplateController extends AbstractController
{
    public function __construct(
        private readonly TemplateRepository $templateRepository,
        private readonly ButtonRepository $buttonRepository,
    ) {
    }

    #[Route('/template/by-type/{type}', name: 'telegram_template_get_by_type', methods: ['GET'])]
    public function getTemplatesByType(string $type, Request $request): JsonResponse
    {
        $botIdentifier = $request->attributes->get('bot_identifier') ?? '';
        $templates = $this->templateRepository->findByTypeAndBotIdentifier($type, $botIdentifier);

        if (empty($templates)) {
            return new JsonResponse(['error' => 'No templates found for this type'], 404);
        }

        return new JsonResponse(array_map(
            fn ($template) => $this->formatTemplate($template, $botIdentifier),
            $templates
        ));
    }

    private function formatTemplate($template, string $botIdentifier): array
    {
        $layout = $template->getLayout();
        $formattedLayout = [];

        // Process each line in the layout
        foreach ($layout as $lineIndex => $line) {
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

            if (!empty($formattedLine)) {
                $formattedLayout[] = $formattedLine;
            }
        }

        return [
            'id' => $template->getId(),
            'name' => $template->getName(),
            'type' => $template->getType(),
            'layout' => $formattedLayout,
        ];
    }
}
