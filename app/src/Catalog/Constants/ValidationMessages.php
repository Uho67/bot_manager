<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Constants;

class ValidationMessages
{
    public const string LINE_MUST_BE_ARRAY = 'Each line in layout must be an array.';
    public const string MAX_BUTTONS_EXCEEDED = 'A line cannot have more than {{ limit }} buttons.';
    public const string INVALID_BUTTON_ID = 'Button ID must be a string or integer.';
}

