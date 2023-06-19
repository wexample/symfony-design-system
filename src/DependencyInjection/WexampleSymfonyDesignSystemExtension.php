<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemBundleInterface;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyHelpers\Helper\ClassHelper;

class WexampleSymfonyDesignSystemExtension extends AbstractWexampleSymfonyExtension
{
    public function load(
        array $configs,
        ContainerBuilder $container
    ) {
        $this->loadConfig(
            __DIR__,
            $container
        );

        $bundles = $container->getParameter('kernel.bundles');

        $paths = [];
        foreach ($bundles as $class) {
            if (ClassHelper::classImplementsInterface(
                $class,
                DesignSystemBundleInterface::class
            )) {
                $bundleFronts = $class::getDesignSystemFrontPaths();

                $realpath = [];
                foreach ($bundleFronts as $frontPath) {
                    $realpath[] = realpath($frontPath).'/';
                }

                $paths[$class] = $realpath;
            }
        }

        $container->setParameter('design_system_packages_front_paths', $paths);

    }
}
