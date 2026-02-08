<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Button\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Button\Entity\Button;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Filter buttons by bot_identifier from current user's JWT token
 */
readonly class ButtonCollectionExtension implements QueryCollectionExtensionInterface
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
        if (Button::class !== $resourceClass) {
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
