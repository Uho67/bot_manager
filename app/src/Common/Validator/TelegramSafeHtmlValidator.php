<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Common\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TelegramSafeHtmlValidator extends ConstraintValidator
{
    private const ALLOWED_TAGS = '<b><strong><i><em><u><s><code><pre><a>';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $stripped = strip_tags((string) $value, self::ALLOWED_TAGS);

        if ($stripped !== (string) $value) {
            // Auto-correct: set the stripped value on the object property
            $object = $this->context->getObject();
            $propertyName = $this->context->getPropertyName();

            if ($object !== null && $propertyName !== null) {
                $setter = 'set' . ucfirst($propertyName);
                if (method_exists($object, $setter)) {
                    $object->$setter($stripped);
                }
            }
        }
    }
}
