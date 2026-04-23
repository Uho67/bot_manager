<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Validator;

use App\Catalog\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Traversable;

class ValidCategoryChildrenValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!($value instanceof Traversable || \is_array($value))) {
            return;
        }

        $currentCategory = $this->getCurrentCategory();

        if (!$currentCategory || !$currentCategory->getId()) {
            return;
        }

        $childrenIds = array_map(static function ($category) {
            return $category->getId();
        }, $value->toArray());

        $childrenIds = array_filter($childrenIds);

        if (\in_array($currentCategory->getId(), $childrenIds, true)) {
            $this->logger->warning('Category self-reference attempt blocked', [
                'category_id' => $currentCategory->getId(),
                'submitted_children' => $childrenIds,
            ]);
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        if (!empty($childrenIds) && $this->isAncestor($childrenIds, $currentCategory)) {
            $this->logger->warning('Circular category reference attempt blocked', [
                'category_id' => $currentCategory->getId(),
                'submitted_children' => $childrenIds,
            ]);
            $this->context->buildViolation($constraint->circularMessage)
                ->addViolation();

            return;
        }
    }

    private function getCurrentCategory(): ?Category
    {
        $parentCategory = $this->context->getObject();
        $parentId = $parentCategory?->getId();

        if (!$parentId) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request && preg_match('#/api/categories/(\d+)#', $request->getPathInfo(), $matches)) {
                $parentId = (int) $matches[1];
            }
        }

        if (!$parentId) {
            return null;
        }

        $parentCategory = $this->entityManager->getRepository(Category::class)->find($parentId);
        if (!$parentCategory) {
            throw new LogicException('Request does not contain valid category ID.');
        }

        return $parentCategory;
    }

    private function isAncestor(array $childrenIds, Category $category): bool
    {
        $qb = $this->entityManager->createQueryBuilder();

        $count = $qb->select('COUNT(c.id)')
            ->from(Category::class, 'c')
            ->where(':category MEMBER OF c.childCategories')
            ->andWhere($qb->expr()->in('c.id', ':possibleAncestors'))
            ->setParameter('category', $category)
            ->setParameter('possibleAncestors', $childrenIds)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
