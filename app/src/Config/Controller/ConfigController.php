<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Controller;

use App\Config\ConfigSchema;
use App\Config\Entity\Config;
use App\Config\Service\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/configs')]
class ConfigController extends AbstractController
{
    public function __construct(
        private readonly ConfigService $configService,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route('', name: 'api_configs_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUserFromAuth();

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            $allConfigs = $this->configService->getAll();
            return $this->json($this->transformConfigs($allConfigs));
        }

        $botIdentifier = $user->getBotIdentifier();
        $configs = $this->configService->getAllForBot($botIdentifier);

        return $this->json($this->transformConfigs($configs));
    }


    private function transformConfigs(array $configs): array
    {
        return array_map(function (Config $config) {
            return [
                'id' => $config->getId(),
                'path' => $config->getPath(),
                'name' => $config->getName(),
                'value' => $config->getValue(),
                'bot_identifier' => $config->getBotIdentifier(),
            ];
        }, $configs);
    }

    #[Route('/{id}', name: 'api_config_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $data = json_decode($request->getContent(), true);
        $value = $data['value'] ?? null;
        $config = $this->configService->getById($id);
        $this->validateParams($value, $config, $user);

        // Update the existing config directly
        $this->configService->updateValue($config, $value);

        return $this->json(['message' => 'Config updated']);
    }

    #[Route('', name: 'api_config_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $data = json_decode($request->getContent(), true);

        $path = $data['path'] ?? null;
        $value = $data['value'] ?? null;
        $name = $data['name'] ?? null;
        $botIdentifier = $user->getBotIdentifier();

        if (!$path || !$name || !$botIdentifier) {
            throw new BadRequestHttpException('Path, name, and bot_identifier are required ' . json_encode($data));
        }

        // Admin can only create configs for their own bot
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            if ($botIdentifier !== $user->getBotIdentifier()) {
                throw new AccessDeniedHttpException('Forbidden');
            }
        }

        $config = $this->configService->set($botIdentifier, $path, $value ?? '', $name);

        return $this->json([
            'id' => $config->getId(),
            'path' => $config->getPath(),
            'name' => $config->getName(),
            'value' => $config->getValue(),
            'bot_identifier' => $config->getBotIdentifier(),
        ], 201);
    }

    /**
     * @return UserInterface
     */
    private function getUserFromAuth(): UserInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user || !method_exists($user, 'getBotIdentifier')) {
            throw new UnauthorizedHttpException('Bearer', 'Not authenticated');
        }

        return $user;
    }

    private function validateParams(string|null $value, Config|null $config, UserInterface $adminUser): void
    {
        if ($value === null) {
            throw new BadRequestHttpException('Value is required');
        }

        if (!$config) {
            throw new NotFoundHttpException('Config not found');
        }

        // Admin can only update their own bot's configs
        if (!in_array('ROLE_SUPER_ADMIN', $adminUser->getRoles(), true)) {
            if ($config->getBotIdentifier() !== $adminUser->getBotIdentifier()) {
                throw new AccessDeniedHttpException('Forbidden');
            }
        }
    }
}
