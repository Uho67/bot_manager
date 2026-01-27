<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Service;

use App\Config\Entity\Config;
use App\Config\Repository\ConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;

readonly class ConfigService
{
    public function __construct(
        private ConfigRepository $configRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function get(string $botIdentifier, string $path, string $default = '0'): string
    {
        $config = $this->configRepository->findByBotIdentifierAndPath($botIdentifier, $path);

        return $config?->getValue() ?? $default;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $botIdentifier, string $path, string $value, string $name): Config
    {
        $config = $this->configRepository->findByBotIdentifierAndPath($botIdentifier, $path);

        if (!$config) {
            $config = new Config();
            $config->setBotIdentifier($botIdentifier);
            $config->setPath($path);
            $config->setName($name);
        }

        $config->setValue($value);

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        // Clear cache after update
        $this->configRepository->clearCache($botIdentifier);

        return $config;
    }

    public function getAllForBot(string $botIdentifier): array
    {
        return $this->configRepository->findByBotIdentifier($botIdentifier);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function clearCache(string $botIdentifier): void
    {
        $this->configRepository->clearCache($botIdentifier);
    }

    public function getAll(): array
    {
        return $this->configRepository->findAll();
    }

    public function getById(int $id): ?Config
    {
        return $this->configRepository->find($id);
    }

    public function updateValue(Config $config, string $value): Config
    {
        $config->setValue($value);
        $this->entityManager->flush();

        $this->configRepository->clearCache($config->getBotIdentifier());

        return $config;
    }
}
