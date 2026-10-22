<?php

namespace Wexample\SymfonyDesignSystem\Avatar;

use Wexample\SymfonyDesignSystem\Helper\AvatarHelper;
use Wexample\SymfonyDesignSystem\Interface\AvatarEngineInterface;

/**
 * The initials of a name on a disc of the seed's colour; the seed's first
 * letters when there is no name.
 */
class InitialsAvatarEngine implements AvatarEngineInterface
{
    public static function getName(): string
    {
        return 'initials';
    }

    public function render(
        string $seed,
        ?string $label = null
    ): string {
        $initials = AvatarHelper::avatarInitials($label ?: $seed);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
            .'<circle cx="50" cy="50" r="50" fill="%s"/>'
            .'<text x="50" y="50" dy=".35em" text-anchor="middle" fill="#fff" font-family="system-ui, sans-serif" font-size="40" font-weight="600">%s</text>'
            .'</svg>',
            AvatarHelper::avatarColor($seed),
            htmlspecialchars($initials, ENT_XML1)
        );
    }
}
