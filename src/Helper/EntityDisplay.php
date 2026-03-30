<?php

namespace Wexample\SymfonyDesignSystem\Helper;

final class EntityDisplay
{
    public const BAR = 'bar';
    public const CARD = 'card';
    public const LIST_ITEM = 'list-item';

    /**
     * Standard display identifiers provided by the design system.
     * Custom displays are allowed and should not be rejected by callers.
     */
    public static function standard(): array
    {
        return [
            self::BAR,
            self::CARD,
            self::LIST_ITEM,
        ];
    }
}

