<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Constants;

class ButtonConstants
{
    public const string TYPE_CALLBACK = 'callback';
    public const string TYPE_URL = 'url';

    public const string PREFIX_CATEGORY = 'category_';
    public const string PREFIX_PRODUCT = 'product_';
    public const string PREFIX_BUTTON = 'button_';

    public const string VALUE_FORMAT_CATEGORY = 'category/%d';
    public const string VALUE_FORMAT_PRODUCT = 'product/%d';

    public const int EXTRACT_CATEGORY_ID_OFFSET = 9;
    public const int EXTRACT_PRODUCT_ID_OFFSET = 8;
}
