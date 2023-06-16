<?php

namespace Wexample\SymfonyDesignSystem\Traits;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;

trait DesignSystemExtensionTrait
{
    protected function setTranslationPath(
        ContainerBuilder $container,
        string $path
    ):void {
        $key = DesignSystemHelper::CONFIG_PARAMETER_FRONTS;
        $designSystemFronts = [];

        if ($container->hasParameter($key)) {
            $designSystemFronts = $container->getParameter($key);
        }

        $designSystemFronts[] = realpath($path) . '/';
        $container->setParameter($key, $designSystemFronts);
    }
}