<?php

namespace Wexample\SymfonyDesignSystem\Helper;

/**
 * The faces of a group as a pile shows them: the first ones, and how many
 * more there are, said as one more face. The twin of
 * assets/js/Helper/AvatarGroupHelper.ts.
 */
final class AvatarGroupHelper
{
    /**
     * @return array{shown: array, more: int}
     */
    public static function split(array $items, int $max): array
    {
        $items = array_values($items);
        $limit = max(1, $max);

        if (count($items) <= $limit) {
            return ['shown' => $items, 'more' => 0];
        }

        return ['shown' => array_slice($items, 0, $limit - 1), 'more' => count($items) - ($limit - 1)];
    }

    public static function initials(?string $label): string
    {
        $words = preg_split('/\s+/u', trim((string) $label), -1, PREG_SPLIT_NO_EMPTY);

        if (! $words) {
            return '';
        }

        $letters = count($words) > 1
            ? mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1)
            : mb_substr($words[0], 0, 2);

        return mb_strtoupper($letters);
    }
}
