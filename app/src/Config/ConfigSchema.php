<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config;

class ConfigSchema
{
    public static function getSchema(): array
    {
        return [
            'bot/admin/link' => [
                'path' => 'bot/admin/link',
                'name' => 'Bot Admin Link',
                'value' => '',
            ],
            'bot/channel/link' => [
                'path' => 'bot/channel/link',
                'name' => 'Bot Cannel Link',
                'value' => '',
            ],
            'order/message/welcome' => [
                'path' => 'order/message/welcome',
                'name' => 'Order Welcome Message',
                'value' => '',
            ],
        ];
    }
}
