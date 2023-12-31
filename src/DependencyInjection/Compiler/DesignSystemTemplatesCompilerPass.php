<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\SymfonyHelpers\AbstractBundle;

readonly class DesignSystemTemplatesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('twig.loader.native_filesystem');
        $bundlesPaths = $container->getParameter('design_system_packages_front_paths');

        /**
         * @var AbstractBundle $bundleClass
         * @var array          $paths
         */
        foreach ($bundlesPaths as $bundleClass => $paths) {
            foreach ($paths as $path) {
                $definition->addMethodCall(
                    'addPath',
                    [
                        $path,
                        class_exists($bundleClass) ?
                        $bundleClass::getAlias() : $bundleClass,
                    ]
                );

                // Add also to allow find all "front" folder, as in translations extension.
                $definition->addMethodCall(
                    'addPath',
                    [
                        $path,
                        DesignSystemHelper::TWIG_NAMESPACE_FRONT,
                    ]
                );
            }
        }
    }
}
