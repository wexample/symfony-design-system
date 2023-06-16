<?php

namespace Wexample\SymfonyDesignSystem\Helper;

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

        foreach (self::TEMPLATES_LOCATIONS as $location)
        {
            $output[] = $location.$relativePath.$pageExtension;
        }

        return $output;
    }
}
