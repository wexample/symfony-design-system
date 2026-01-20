<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\Helpers\Helper\ClassHelper;

class MenuExtension extends AbstractTemplateExtension
{
    public function __construct(
        private readonly RouterInterface $router
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'menu_item',
                function (
                    Environment $twig,
                    string $icon,
                    string $label,
                    string $route,
                    array $routeParams = [],
                    array $options = []
                ) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/menu-item.html.twig',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'route' => $route,
                            'route_params' => $routeParams,
                            'href' => $twig->getFunction('path')->getCallable()($route, $routeParams),
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'menu_item_link',
                function (Environment $twig, string $icon, string $label, string $href, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/menu-item.html.twig',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'href' => $href,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'menu_get_routes_from_controller_namespace',
                [$this, 'menuGetRoutesFromControllerNamespace']
            ),
        ];
    }

    public function menuGetRoutesFromControllerNamespace(string $namespace): array
    {
        // Build a top-level menu from a controller namespace by keeping only root routes
        // and the "index" entrypoint of nested controllers.
        $routes = [];
        $prefix = ClassHelper::normalizeNamespacePrefix($namespace);

        foreach ($this->router->getRouteCollection() as $name => $route) {
            $defaults = $route->getDefaults();
            if (! isset($defaults['_controller'])) {
                continue;
            }

            $controller = ClassHelper::getClassPath($defaults['_controller']);
            if (! $controller || ! str_starts_with($controller, $prefix)) {
                continue;
            }

            if ($this->isEntryPointRoute($controller, $defaults, $prefix)) {
                $routes[$name] = $route;
            }
        }

        return $routes;
    }

    private function isEntryPointRoute(
        string $controller,
        array $defaults,
        string $prefix
    ): bool {
        $depth = ClassHelper::getNamespaceDepth($controller, $prefix);

        if ($depth === 0) {
            return true;
        }

        return ($defaults['routeName'] ?? null) === 'index';
    }
}
