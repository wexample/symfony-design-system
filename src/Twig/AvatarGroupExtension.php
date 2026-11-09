<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Helper\AvatarGroupHelper;

/**
 * What the avatar-group template reads: the faces shown and the count of the
 * rest, the letters of a face with no picture (AvatarGroupHelper).
 */
class AvatarGroupExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('avatar_group_split', AvatarGroupHelper::split(...)),
            new TwigFunction('avatar_initials', AvatarGroupHelper::initials(...)),
        ];
    }
}
