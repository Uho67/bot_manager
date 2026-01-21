<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\AdminUser\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\AdminUser\Entity\AdminUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        null|Operation $operation = null,
        array $context = []
    ): void {
        if (AdminUser::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof AdminUser) {
            return;
        }

        // If user is SUPER_ADMIN, show all users
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return;
        }

        // If user is only ROLE_ADMIN, show only themselves
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.id = :current_user_id', $rootAlias))
            ->setParameter('current_user_id', $user->getId());
    }
}
