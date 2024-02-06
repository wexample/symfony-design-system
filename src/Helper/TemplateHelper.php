<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\SymfonyHelpers\Helper\BundleHelper;

class TemplateHelper
{
    public const TEMPLATE_FILE_EXTENSION = '.html.twig';

    public static function trimTemplateLocationAlias(string $pagePath): string
    {
        if (str_starts_with($pagePath, BundleHelper::ALIAS_PREFIX)) {
            foreach (TemplateHelper::TEMPLATES_LOCATIONS as $location) {
                if ($location && str_starts_with($pagePath, $location)) {
                    return substr($pagePath, strlen($location));
                }
            }
        }

        return $pagePath;
    }
}
