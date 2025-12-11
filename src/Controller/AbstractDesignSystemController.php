<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyHelpers\Helper\TemplateHelper;

abstract class AbstractDesignSystemController extends AbstractController
{
    /**
     * @return string Allow bundle-specific front template directories.
     */
    public static function getTemplateLocationPrefix(): string
    {
        $bundleClass = static::getControllerBundle();

        return ($bundleClass ? $bundleClass::getAlias() : DesignSystemHelper::TWIG_NAMESPACE_FRONT);
    }

    /**
     * Based on the controller name, find the matching template dir.
     * The controller and its templates should follow the same directories structure.
     * ex:
     *   - Config/DesignSystem/AppController.php
     *   - config/design_system/app/(index.html.twig)
     * @return string
     */
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
