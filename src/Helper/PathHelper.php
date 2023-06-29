<?php

namespace Wexample\SymfonyDesignSystem\Helper;

class PathHelper
{
    public static function relativeTo(
        string $path,
        string $basePath
    ): string {
        return \preg_replace(
            '/^'.\str_replace('/', '\/', $basePath).'/',
            '',
            $path
        );
    }
}
