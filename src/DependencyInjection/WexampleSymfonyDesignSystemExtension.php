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
        $projectDir = $container->getParameter('kernel.project_dir');

        $paths = [];
        foreach ($bundles as $class) {
            if (ClassHelper::classImplementsInterface(
                $class,
                DesignSystemBundleInterface::class
            )) {
                $bundleFronts = $class::getDesignSystemFrontPaths();

                $realpath = [];
                foreach ($bundleFronts as $frontPath) {
                    // On supprime le chemin du projet du chemin absolu du fichier
                    $relativePath = '.' . str_replace($projectDir, '', realpath($frontPath)) . '/';
                    $realpath[] = $relativePath;
                }

                $paths[$class] = $realpath;
            }
        }

        $container->setParameter('design_system_packages_front_paths', $paths);
    }
}
