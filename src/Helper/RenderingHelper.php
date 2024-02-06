<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use function implode;

class RenderingHelper
{
    public const CONTEXT_COMPONENT = VariableHelper::COMPONENT;

    public const CONTEXT_LAYOUT = VariableHelper::LAYOUT;

    public const CONTEXT_PAGE = VariableHelper::PAGE;

    public const CONTEXT_VUE = VariableHelper::VUE;

    public const PLACEHOLDER_PRELOAD_TAG = '<-- {{ ADAPTIVE_PRELOAD_PLACEHOLDER }} -->';

    public static function buildRenderContextKey(
        string $renderContextType,
        string $renderContextName
    ): string {
        return implode('@', [
            $renderContextType,
            $renderContextName,
        ]);
    }

    public static function renderNodeNameFromPath(string $templatePath): string
    {
        // Define template name.
        $ext = TemplateHelper::TEMPLATE_FILE_EXTENSION;
        // Remove the leading @SomeThing.
        $templatePath = TextHelper::trimFirstChunkIfMoreThanOne($templatePath, '/');

        // Path have extension.
        if (str_ends_with($templatePath, $ext)) {
            $templatePath = substr(
                $templatePath,
                0,
                -strlen(TemplateHelper::TEMPLATE_FILE_EXTENSION)
            );
        }

        return TemplateHelper::trimTemplateLocationAlias($templatePath);
    }
}
