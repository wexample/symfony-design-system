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
    public static function getTemplateLocationPrefix(
        string $bundle = null
    ): string
    {
        return ($bundle ? DesignSystemHelper::TWIG_NAMESPACE_ASSETS : DesignSystemHelper::TWIG_NAMESPACE_FRONT);
    }

    /**
     * Based on the controller name, find the matching template dir.
     * The controller and its templates should follow the same directories structure.
     * ex:
     *   - Config/DesignSystem/AppController.php
     *   - config/design_system/app/(index.html.twig)
     */
    public static function getControllerTemplateDir(
        string $bundle = null
    ): string
    {
        return TemplateHelper::joinNormalizedParts(
            [
                self::getTemplateLocationPrefix(bundle: $bundle),
                ...TemplateHelper::explodeControllerNamespaceSubParts(
                    controllerName: static::class,
                    bundleClassPath: $bundle
                ),
            ]
        );
    }
}
