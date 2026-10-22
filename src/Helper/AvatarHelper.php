<?php

namespace Wexample\SymfonyDesignSystem\Helper;

/**
 * What an avatar engine derives from its seed. Each choice reads the seed
 * hashed with a word of its own, so the colour and the glyph of a face do not
 * move together from one seed to the next.
 */
class AvatarHelper
{
    /**
     * A number from 0 to `$count - 1`, the same for the same seed and word.
     */
    public static function avatarPick(
        string $seed,
        string $word,
        int $count
    ): int {
        return crc32($word.':'.$seed) % max(1, $count);
    }

    /**
     * A colour of the seed, as a hue with a lightness and a saturation fixed
     * where white stands out on it.
     */
    public static function avatarColor(
        string $seed,
        int $saturation = 55,
        int $lightness = 45
    ): string {
        return sprintf('hsl(%d, %d%%, %d%%)', self::avatarPick($seed, 'hue', 360), $saturation, $lightness);
    }

    /**
     * Up to two letters of a name: the first of its first two words, or its
     * first two when it is one word. Whatever is no letter nor digit only
     * separates words.
     */
    public static function avatarInitials(string $label): string
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', trim($label), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) > 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($words[0] ?? '', 0, 2));
    }
}
