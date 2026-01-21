<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Config\Entity\Config;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class ConfigCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?\ApiPlatform\Metadata\Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== Config::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return; // No filter for super admin
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $user = $this->security->getUser();
            if ($user && method_exists($user, 'getBotIdentifier')) {
                $queryBuilder->andWhere('o.bot_identifier = :bot_identifier')
                   ->setParameter('bot_identifier', $user->getBotIdentifier());
            }
        }
    }
}

