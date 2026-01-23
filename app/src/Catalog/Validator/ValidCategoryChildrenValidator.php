<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Validator;

use App\Catalog\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidCategoryChildrenValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!($value instanceof \Traversable || is_array($value))) {
            return;
        }
        $childrenIds = array_map(function ($category) {
            return $category->getId();
        }, $value->toArray());
        $currentCategory = $this->getCurrentCategory();

        // Check if category is trying to be its own child
        if (in_array($currentCategory->getId(), $childrenIds)) {
            throw new \LogicException('A category cannot be its own child.');
        }

        // Check if any child is an ancestor of the parent
        if ($this->isAncestor($childrenIds, $currentCategory)) {
            throw new \LogicException('Check children for circular reference.');
        }
    }

    private function getCurrentCategory(): Category
    {
        // Try to get parent ID from context object first
        $parentCategory = $this->context->getObject();
        $parentId = $parentCategory?->getId();

        // If no ID from context, try to extract from request URI (for PUT/PATCH requests)
        if (!$parentId) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request && preg_match('#/api/categories/(\d+)#', $request->getPathInfo(), $matches)) {
                $parentId = (int)$matches[1];
            }
        }

        // Fetch the parent category from database
        $parentCategory = $this->entityManager->getRepository(Category::class)->find($parentId);
        if (!$parentCategory) {
            throw new \LogicException('Request does not contain valid category ID.');
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

