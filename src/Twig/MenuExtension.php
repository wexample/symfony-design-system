<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\Helpers\Helper\ClassHelper;

class MenuExtension extends AbstractTemplateExtension
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'menu_item',
                function (
                    Environment $twig,
                    string $route,
                    array $routeParams = [],
                    array $options = [],
                ) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/partials/menu-item.html.twig',
                        [
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
                'menu_separator',
                function (Environment $twig, string $label, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/partials/menu-separator.html.twig',
                        [
                            'label' => $label,
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
                        '@WexampleSymfonyDesignSystemBundle/partials/menu-item-link.html.twig',
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
                'menu_item_collapsible_from_controller',
                function (
                    Environment $twig,
                    mixed $renderPass,
                    string $iconName,
                    string $label,
                    string $href,
                    string $controllerNamespace,
                ) {
                    $routes = $this->menuGetRoutesFromControllerNamespace($controllerNamespace);

                    $currentRoute = $this->requestStack->getCurrentRequest()?->attributes->get('_route', '');
                    $prefix = ClassHelper::normalizeNamespacePrefix($controllerNamespace);
                    $isOpen = false;

                    foreach ($this->router->getRouteCollection() as $name => $route) {
                        if ($name !== $currentRoute) {
                            continue;
                        }
                        $controller = ClassHelper::getClassPath($route->getDefaults()['_controller'] ?? '');
                        if ($controller && str_starts_with($controller, $prefix)) {
                            $isOpen = true;
                        }
                        break;
                    }

                    $pathFn = $twig->getFunction('path')->getCallable();
                    $items = '';
                    foreach ($routes as $routeName => $route) {
                        $routeHref = $pathFn($routeName);
                        if ($routeHref === $href) {
                            continue;
                        }
                        $items .= $this->renderTemplate(
                            $twig,
                            '@WexampleSymfonyDesignSystemBundle/partials/menu-item.html.twig',
                            [
                                'route' => $routeName,
                                'route_params' => [],
                                'href' => $routeHref,
                                'options' => [],
                            ]
                        );
                    }

                    $content = $items ? '<ul class="menu--sub-items">'.$items.'</ul>' : '';

                    if ($content === '') {
                        return $this->renderTemplate(
                            $twig,
                            '@WexampleSymfonyDesignSystemBundle/partials/menu-item-link.html.twig',
                            [
                                'icon' => $iconName,
                                'label' => $label,
                                'href' => $href,
                                'options' => [],
                            ]
                        );
                    }

                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/partials/menu-item-collapsible.html.twig',
                        [
                            'render_pass' => $renderPass,
                            'icon_name' => $iconName,
                            'label' => $label,
                            'href' => $href,
                            'content' => $content,
                            'is_open' => $isOpen,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'menu_item_collapsible',
                function (
                    Environment $twig,
                    mixed $renderPass,
                    string $iconName,
                    string $label,
                    string $href,
                    string $content,
                    bool $isOpen = false,
                ) {
                    if (trim($content) === '') {
                        return $this->renderTemplate(
                            $twig,
                            '@WexampleSymfonyDesignSystemBundle/partials/menu-item-link.html.twig',
                            [
                                'icon' => $iconName,
                                'label' => $label,
                                'href' => $href,
                                'options' => [],
                            ]
                        );
                    }

                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/partials/menu-item-collapsible.html.twig',
                        [
                            'render_pass' => $renderPass,
                            'icon_name' => $iconName,
                            'label' => $label,
                            'href' => $href,
                            'content' => $content,
                            'is_open' => $isOpen,
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
