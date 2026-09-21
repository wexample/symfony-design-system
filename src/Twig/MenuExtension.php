<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\Helpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Service\MenuItemRegistry;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class MenuExtension extends AbstractTemplateExtension
{
    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly MenuItemRegistry $menuItemRegistry,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'menu_item',
                function (
                    Environment $twig,
                    $context,
                    string $route,
                    array $routeParams = [],
                    array $options = [],
                ) {
                    return $this->renderMenuItem($twig, $context, $route, $routeParams, $options);
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'menu_items',
                function (
                    Environment $twig,
                    $context,
                    string $group,
                    array $routeParams = [],
                    array $options = [],
                ) {
                    $rendered = '';

                    foreach ($this->menuItemRegistry->getGroup($group) as $route) {
                        $rendered .= $this->renderMenuItem($twig, $context, $route, $routeParams, $options);
                    }

                    // The empty string of a group nobody joined is what lets a
                    // template put a heading above the run only when there is a
                    // run: `{% set items = menu_items(...) %}` then reads false.
                    return $rendered;
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'menu_separator',
                function (Environment $twig, $context, string $label, array $options = []) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/menu-separator',
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
                function (Environment $twig, $context, string $icon, string $label, string $href, array $options = []) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/menu-item-link',
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
                    $context,
                    mixed $renderPass,
                    string $controllerNamespace,
                ) {
                    $routes = $this->menuGetRoutesFromControllerNamespace($controllerNamespace);
                    $indexRoute = AbstractController::findIndexRoute($this->router, $controllerNamespace);

                    $currentRoute = $this->requestStack->getCurrentRequest()?->attributes->get('_route', '');
                    $prefix = ClassHelper::normalizeNamespacePrefix($controllerNamespace);
                    $isOpen = false;

                    foreach ($this->router->getRouteCollection() as $name => $route) {
                        if ($name !== $currentRoute) {
                            continue;
                        }
                        $controller = ClassHelper::getClassPath($route->getDefaults()['_controller'] ?? '');
                        if ($controller && ClassHelper::classPathMatchesPrefix($controller, $prefix)) {
                            $isOpen = true;
                        }

                        break;
                    }

                    $pathFn = $twig->getFunction('path')->getCallable();
                    $href = $pathFn($indexRoute);

                    $items = '';
                    foreach ($routes as $routeName => $route) {
                        $routeHref = $pathFn($routeName);
                        if ($routeHref === $href) {
                            continue;
                        }
                        $items .= $this->renderComponent(
                            $twig,
                            $context,
                            '@WexampleSymfonyDesignSystemBundle/components/menu-item',
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
                        return $this->renderComponent(
                            $twig,
                            $context,
                            '@WexampleSymfonyDesignSystemBundle/components/menu-item',
                            [
                                'route' => $indexRoute,
                                'route_params' => [],
                                'href' => $href,
                                'options' => [],
                            ]
                        );
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/menu-item-collapsible',
                        [
                            'render_pass' => $renderPass,
                            'route' => $indexRoute,
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
                    $context,
                    mixed $renderPass,
                    string $iconName,
                    string $label,
                    string $href,
                    string $content,
                    bool $isOpen = false,
                ) {
                    if (trim($content) === '') {
                        return $this->renderComponent(
                            $twig,
                            $context,
                            '@WexampleSymfonyDesignSystemBundle/components/menu-item-link',
                            [
                                'icon' => $iconName,
                                'label' => $label,
                                'href' => $href,
                                'options' => [],
                            ]
                        );
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/menu-item-collapsible',
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

    /**
     * One item pointing at one route — what both the named item and a whole
     * group are made of, so that a change to either is a change to both.
     */
    private function renderMenuItem(
        Environment $twig,
        mixed $context,
        string $route,
        array $routeParams,
        array $options,
    ): string {
        return $this->renderComponent(
            $twig,
            $context,
            '@WexampleSymfonyDesignSystemBundle/components/menu-item',
            [
                'route' => $route,
                'route_params' => $routeParams,
                'href' => $twig->getFunction('path')->getCallable()($route, $routeParams),
                'options' => $options,
            ]
        );
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
            if (! $controller || ! ClassHelper::classPathMatchesPrefix($controller, $prefix)) {
                continue;
            }

            if ($this->isEntryPointRoute($controller, $defaults, $prefix, $name, $route)) {
                $routes[$name] = $route;
            }
        }

        return $routes;
    }

    private function isEntryPointRoute(
        string $controller,
        array $defaults,
        string $prefix,
        string $routeName,
        Route $route,
    ): bool {
        $depth = ClassHelper::getNamespaceDepth($controller, $prefix);

        if ($depth === 0) {
            return true;
        }

        return ($defaults['routeName'] ?? null) === AbstractController::DEFAULT_ROUTE_NAME_INDEX;
    }
}
