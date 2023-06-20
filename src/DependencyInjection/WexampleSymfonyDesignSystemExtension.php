<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemBundleInterface;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

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

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $bundles = $container->getParameter('kernel.bundles');
        $paths = [];

        foreach ($config['front_paths'] as $frontPath) {
            $paths[VariableHelper::APP][] = realpath($frontPath).FileHelper::FOLDER_SEPARATOR;
        }

        foreach ($bundles as $class) {
            if (ClassHelper::classImplementsInterface(
                $class,
                DesignSystemBundleInterface::class
            )) {
                $bundleFronts = $class::getDesignSystemFrontPaths();

                $realpath = [];
                foreach ($bundleFronts as $frontPath) {
                    $relativePath = realpath($frontPath).FileHelper::FOLDER_SEPARATOR;
                    $realpath[] = $relativePath;
                }

                $paths[$class] = $realpath;
            }
        }

        $container->setParameter('design_system_packages_front_paths', $paths);
    }
}
