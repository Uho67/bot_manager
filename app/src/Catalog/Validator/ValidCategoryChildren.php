<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ValidCategoryChildren extends Constraint
{
    public string $message = 'A category cannot have itself or its parent as a child.';
    public string $circularMessage = 'Circular reference detected: Category cannot be its own ancestor.';
}

