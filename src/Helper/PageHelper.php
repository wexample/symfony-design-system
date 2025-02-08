<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\Helpers\Helper\TextHelper;

class PageHelper
{
    public static function joinNormalizedParts(
        array $parts,
        string $separator = '/'
    ): string {
        return implode(
            $separator,
            array_map([TextHelper::class, 'toSnake'], $parts
            )
        );
    }

    public static function explodeControllerNamespaceSubParts(
        string $controllerName,
        string $bundleClassPath = null
    ): array {
        $controllerName = AbstractController::removeSuffix($controllerName);
        $parts = explode('\\', $controllerName);

        if ($bundleClassPath) {
            $spliceCount = count(explode('\\', $bundleClassPath));
        } else {
            $spliceCount = 2;
        }

        return array_splice($parts, $spliceCount);
    }
}
