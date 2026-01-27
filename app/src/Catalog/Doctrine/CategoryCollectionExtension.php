<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Catalog\Entity\Category;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CategoryCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (Category::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        $botIdentifier = $user->getBotIdentifier();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(\sprintf('%s.bot_identifier = :bot_identifier', $rootAlias))
            ->setParameter('bot_identifier', $botIdentifier);
    }
}
