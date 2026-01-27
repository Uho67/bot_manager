<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Controller;

use App\Config\ConfigSchema;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/config')]
class ConfigSchemaController extends AbstractController
{
    #[Route('/schema', name: 'api_config_schema', methods: ['GET'])]
    public function getSchema(): JsonResponse
    {
        return $this->json(\array_values(ConfigSchema::getSchema()));
    }
}
