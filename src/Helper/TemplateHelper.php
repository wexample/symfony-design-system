<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\SymfonyHelpers\Helper\BundleHelper;

class TemplateHelper
{
    public const TEMPLATE_FILE_EXTENSION = '.html.twig';

    public const TEMPLATES_LOCATIONS = [
        // Search in default local template folder.
        '',
        // Search in base bundle.
        DesignSystemHelper::FOLDER_FRONT_ALIAS,
    ];

    public static function buildTemplateInheritanceStack(
        string $relativePath,
        string $pageExtension = self::TEMPLATE_FILE_EXTENSION
    ): array {
        $output = [];

        foreach (self::TEMPLATES_LOCATIONS as $location) {
            $output[] = $location.$relativePath.$pageExtension;
        }

        return $output;
    }

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
