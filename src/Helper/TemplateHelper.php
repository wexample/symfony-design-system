<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\Helpers\Helper\TextHelper;

class TemplateHelper
{
    public const TEMPLATE_FILE_EXTENSION = '.html.twig';

    protected const string VIEW_PATH_PREFIX = '@';

    public static function removeExtension(string $viewPAthWithExtension): string
    {
        return TextHelper::removeSuffix(
            $viewPAthWithExtension,
            self::TEMPLATE_FILE_EXTENSION
        );
    }

    public static function trimPathPrefix(
        string $domain,
    ): string
    {
        return substr($domain, strlen(self::VIEW_PATH_PREFIX));
    }
}
