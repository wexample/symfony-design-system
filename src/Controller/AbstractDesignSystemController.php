<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyHelpers\Helper\TemplateHelper;

abstract class AbstractDesignSystemController extends AbstractController
{
    public static function getTemplateLocationPrefix(): string
    {
        $bundleClass = static::getControllerBundle();

        return ($bundleClass ? $bundleClass::getAlias() : DesignSystemHelper::TWIG_NAMESPACE_FRONT);
    }

    public static function getControllerTemplateDir(): string
    {
        return TemplateHelper::joinNormalizedParts(
            [
                self::getTemplateLocationPrefix(),
                ...TemplateHelper::explodeControllerNamespaceSubParts(static::class),
            ]
        );
    }
}
