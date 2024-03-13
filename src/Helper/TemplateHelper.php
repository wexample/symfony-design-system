<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\SymfonyHelpers\Helper\TextHelper;

class TemplateHelper
{
    public const TEMPLATE_FILE_EXTENSION = '.html.twig';

    public static function removeExtension(string $viewPAthWithExtension): string
    {
        return TextHelper::removeSuffix(
            $viewPAthWithExtension,
            self::TEMPLATE_FILE_EXTENSION
        );
    }
}
