<?php

namespace Wexample\SymfonyDesignSystem\Helper;

class RenderingHelper
{
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
}
