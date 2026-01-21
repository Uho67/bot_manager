<?php

namespace App\Bot\Doctrine\Extension;

use App\Bot\Entity\Bot;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class BotCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        null|Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== Bot::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return; // No filter for super admin
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $user = $this->security->getUser();
            if (method_exists($user, 'getBotIdentifier')) {
                $queryBuilder->andWhere('o.bot_identifier = :bot_identifier')
                    ->setParameter('bot_identifier', $user->getBotIdentifier());
            }
        }
    }
}
