<?php

declare(strict_types=1);

namespace App\Bot\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Bot\Entity\Bot;
use Doctrine\ORM\QueryBuilder;
use LogicException;
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
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (Bot::class !== $resourceClass) {
            return;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return;
        }
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new LogicException('Access denied.');
        }
        $user = $this->security->getUser();
        $queryBuilder->andWhere('o.bot_identifier = :bot_identifier')
            ->setParameter('bot_identifier', $user->getBotIdentifier());
    }
}
