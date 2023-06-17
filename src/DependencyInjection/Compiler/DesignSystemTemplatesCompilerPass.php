<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

readonly class DesignSystemTemplatesCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private string $frontPath,
        private string $alias,
    ) {

    }

    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('twig.loader.native_filesystem');

        $definition->addMethodCall(
            'addPath',
            [
                $this->frontPath,
                $this->alias,
            ]
        );
    }
}
