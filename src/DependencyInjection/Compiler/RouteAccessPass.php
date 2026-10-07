<?php

namespace Wexample\SymfonyDesignSystem\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Wexample\SymfonyDesignSystem\Service\RouteAccessService;

/**
 * Hands RouteAccessService what the security bundle decides with, when the
 * application has one: the design system does not require it, and a service
 * asking for it by id would break every application that has none.
 */
final class RouteAccessPass implements CompilerPassInterface
{
    private const array SERVICES = [
        '$accessMap' => 'security.access_map',
        '$accessDecisionManager' => 'security.access.decision_manager',
        '$tokenStorage' => 'security.token_storage',
    ];

    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition(RouteAccessService::class)) {
            return;
        }

        $definition = $container->getDefinition(RouteAccessService::class);

        foreach (self::SERVICES as $argument => $id) {
            if ($container->has($id)) {
                $definition->setArgument($argument, new Reference($id));
            }
        }
    }
}
