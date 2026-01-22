<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Validator;

use App\Catalog\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidCategoryChildrenValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidCategoryChildren) {
            throw new UnexpectedTypeException($constraint, ValidCategoryChildren::class);
        }

        if (null === $value || !($value instanceof \Traversable || is_array($value))) {
            return;
        }

        // Get the parent category being validated
        $parentCategory = $this->context->getObject();

        if (!$parentCategory instanceof Category) {
            return;
        }

        // If the category doesn't have an ID yet, it's new - skip validation
        if (!$parentCategory->getId()) {
            return;
        }

        $parentId = $parentCategory->getId();

        foreach ($value as $child) {
            if (!$child instanceof Category) {
                continue;
            }

            // Check if trying to add itself as a child
            if ($child->getId() === $parentId) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ categoryId }}', (string)$parentId)
                    ->addViolation();
                return;
            }

            // Check if the child is actually a parent of this category (circular reference)
            if ($this->isAncestor($child, $parentCategory)) {
                $this->context->buildViolation($constraint->circularMessage)
                    ->setParameter('{{ categoryId }}', (string)$child->getId())
                    ->addViolation();
                return;
            }
        }
    }

    /**
     * Check if $possibleAncestor is an ancestor of $category
     */
    private function isAncestor(Category $possibleAncestor, Category $category): bool
    {
        // Get fresh data from database to avoid stale collection data
        $categoryFromDb = $this->entityManager->find(Category::class, $category->getId());

        if (!$categoryFromDb) {
            return false;
        }

        // Check if the category we're checking is in the children of possibleAncestor
        foreach ($possibleAncestor->getChildCategories() as $child) {
            if ($child->getId() === $category->getId()) {
                return true;
            }

            // Recursively check children (prevent infinite loops by limiting depth)
            if ($this->isAncestorRecursive($child, $category, 0, 10)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursive check with depth limit to prevent infinite loops
     */
    private function isAncestorRecursive(Category $current, Category $target, int $depth, int $maxDepth): bool
    {
        if ($depth >= $maxDepth) {
            return false;
        }

        foreach ($current->getChildCategories() as $child) {
            if ($child->getId() === $target->getId()) {
                return true;
            }

            if ($this->isAncestorRecursive($child, $target, $depth + 1, $maxDepth)) {
                return true;
            }
        }

        return false;
    }
}

