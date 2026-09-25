<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Throwable;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\Helpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class BreadcrumbExtension extends AbstractTemplateExtension
{
    private const STACK_ATTRIBUTE = '_breadcrumb_stack';

    /**
     * Where the pages of an app or a bundle start: the namespaces above it are
     * the bundle's, not steps of a trail.
     */
    private const PAGES_NAMESPACE = 'Controller'.ClassHelper::NAMESPACE_SEPARATOR.'Pages';

    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'breadcrumb',
                function (
                    Environment $twig,
                    $context,
                    array $items,
                    array $options = [],
                ) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/breadcrumb',
                        [
                            'items' => $this->normalizeItems($items),
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'breadcrumb_append_route',
                [$this, 'breadcrumbAppendRoute']
            ),
            new TwigFunction(
                'breadcrumb_stack',
                [$this, 'breadcrumbStack']
            ),
            new TwigFunction(
                'breadcrumb_render',
                function (Environment $twig, $context, array $options = []) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/breadcrumb',
                        [
                            'items' => $this->normalizeItems($this->buildStackWithCurrentRoute()),
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    public function breadcrumbAppendRoute(string $route, array $params = [], ?string $label = null): void
    {
        $request = $this->getCurrentRequest();
        if (! $request) {
            return;
        }

        $stack = $this->getStack($request);
        $stack[] = [
            'route' => $route,
            'params' => $params,
            'label' => $label,
        ];

        $request->attributes->set(self::STACK_ATTRIBUTE, $stack);
    }

    public function breadcrumbStack(): array
    {
        $request = $this->getCurrentRequest();
        if (! $request) {
            return [];
        }

        return $this->buildStackWithCurrentRoute();
    }

    private function buildStackWithCurrentRoute(): array
    {
        $request = $this->getCurrentRequest();
        if (! $request) {
            return [];
        }

        $stack = $this->getStack($request);
        $currentRoute = $request->attributes->get('_route');

        // Nothing appended by hand: the trail is read from where the page's
        // controller stands. A template appending routes keeps the trail it
        // wrote, as the apps that build theirs layout by layout do.
        if (empty($stack) && is_string($currentRoute)) {
            $stack = $this->buildAncestors(
                $currentRoute,
                $request->attributes->get('_route_params', [])
            );
        }

        if ($currentRoute) {
            $last = end($stack);
            $lastRoute = is_array($last) ? ($last['route'] ?? null) : null;

            if ($lastRoute !== $currentRoute) {
                $stack[] = [
                    'route' => $currentRoute,
                    'params' => $request->attributes->get('_route_params', []),
                ];
            }
        }

        return $stack;
    }

    /**
     * The pages above the current one, read from its controller's namespace as
     * the menu reads it: under `Controller\Pages`, each namespace level gives
     * its index route (`findIndexRoute`), then the controller gives its own.
     * A level's index is kept only when its controller stands above the
     * current one — a sibling is where the level lands, not where the page
     * hangs from — and only when it can be built from the current parameters.
     *
     * @return array<array{route: string, params: array}>
     */
    private function buildAncestors(string $currentRoute, array $currentParams): array
    {
        $route = $this->router->getRouteCollection()->get($currentRoute);
        $controller = $route ? ClassHelper::getClassPath($route->getDefaults()['_controller'] ?? '') : '';
        $separator = ClassHelper::NAMESPACE_SEPARATOR;
        $pagesAt = strpos($controller, $separator.self::PAGES_NAMESPACE.$separator);

        if (false === $pagesAt) {
            return [];
        }

        $root = substr($controller, 0, $pagesAt + strlen($separator.self::PAGES_NAMESPACE));
        $controllerNamespace = substr($controller, 0, (int) strrpos($controller, $separator));
        $levels = array_filter(explode($separator, substr($controllerNamespace, strlen($root))));

        $candidates = [];
        $namespace = $root;

        foreach ($levels as $level) {
            $namespace .= $separator.$level;
            $candidates[] = AbstractController::findIndexRoute($this->router, $namespace);
        }

        $candidates[] = $this->findControllerIndexRoute($controller);

        $ancestors = [];
        $seen = [$currentRoute => true];

        foreach (array_filter($candidates) as $candidate) {
            if (isset($seen[$candidate]) || ! $this->standsAbove($candidate, $controller)) {
                continue;
            }

            $params = $this->paramsFor($candidate, $currentParams);

            if (null === $params) {
                continue;
            }

            $seen[$candidate] = true;
            $ancestors[] = ['route' => $candidate, 'params' => $params];
        }

        return $ancestors;
    }

    private function findControllerIndexRoute(string $controller): ?string
    {
        foreach ($this->router->getRouteCollection() as $name => $route) {
            if (ClassHelper::getClassPath($route->getDefaults()['_controller'] ?? '') === $controller
                && str_ends_with($name, '_'.AbstractController::DEFAULT_ROUTE_NAME_INDEX)) {
                return $name;
            }
        }

        return null;
    }

    // The controller of `$route` is the current one, or one whose namespace
    // holds the current controller's.
    private function standsAbove(string $route, string $controller): bool
    {
        $candidate = $this->router->getRouteCollection()->get($route);
        $candidateController = $candidate ? ClassHelper::getClassPath($candidate->getDefaults()['_controller'] ?? '') : '';

        if ($candidateController === $controller) {
            return true;
        }

        $separator = ClassHelper::NAMESPACE_SEPARATOR;
        $candidateNamespace = substr($candidateController, 0, (int) strrpos($candidateController, $separator));
        $controllerNamespace = substr($controller, 0, (int) strrpos($controller, $separator));

        return '' !== $candidateNamespace
            && $candidateNamespace !== $controllerNamespace
            && str_starts_with($controllerNamespace.$separator, $candidateNamespace.$separator);
    }

    // The current parameters the route asks for, or null when it asks for one
    // the current page does not have.
    private function paramsFor(string $route, array $currentParams): ?array
    {
        $compiled = $this->router->getRouteCollection()->get($route)?->compile();
        $params = array_intersect_key($currentParams, array_flip($compiled?->getPathVariables() ?? []));

        try {
            $this->router->generate($route, $params);
        } catch (Throwable) {
            return null;
        }

        return $params;
    }

    private function getStack(Request $request): array
    {
        $stack = $request->attributes->get(self::STACK_ATTRIBUTE, []);

        return is_array($stack) ? $stack : [];
    }

    private function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $normalized[] = [
                    'route' => $item,
                    'params' => [],
                ];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $normalized[] = [
                'route' => $item['route'] ?? null,
                'params' => $item['params'] ?? [],
                'label' => $item['label'] ?? null,
                'current' => $item['current'] ?? false,
            ];
        }

        return $normalized;
    }
}
