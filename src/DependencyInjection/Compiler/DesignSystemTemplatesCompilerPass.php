<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyDesignSystem\AbstractDesignSystemBundle;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;

readonly class DesignSystemTemplatesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('twig.loader.native_filesystem');
        $bundlesPaths = $container->getParameter('design_system_packages_front_paths');

        /**
         * @var AbstractDesignSystemBundle $bundleClass
         * @var array                      $paths
         */
        foreach ($bundlesPaths as $bundleClass => $paths) {
            foreach ($paths as $path) {
                $definition->addMethodCall(
                    'addPath',
                    [
                        $path,
                        $bundleClass::getAlias(),
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
