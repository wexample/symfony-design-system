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
            // Ignore invalid paths.
            if ($realpath = realpath($frontPath)) {
                $paths[VariableHelper::APP][] = $realpath.FileHelper::FOLDER_SEPARATOR;
            }
        }

        foreach ($bundles as $class) {
            if (ClassHelper::classImplementsInterface(
                $class,
                DesignSystemBundleInterface::class
            )) {
                $bundleFronts = $class::getDesignSystemFrontPaths();

                $realpath = [];
                foreach ($bundleFronts as $alias => $frontPath) {
                    $relativePath = realpath($frontPath).FileHelper::FOLDER_SEPARATOR;

                    if (is_string($alias)) {
                        $realpath[$alias] = $relativePath;
                    } else {
                        $realpath[] = $relativePath;
                    }
                }

                $paths[$class] = $realpath;
            }
        }

        $container->setParameter('design_system_packages_front_paths', $paths);
    }
}
