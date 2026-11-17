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
use Wexample\SymfonyLoader\Controller\AbstractLoaderController;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class BreadcrumbExtension extends AbstractTemplateExtension
{
    private const STACK_ATTRIBUTE = '_breadcrumb_stack';

    private const DESCRIBED_ATTRIBUTE = '_breadcrumb_described';

    // The names a page gives the levels above it (`breadcrumb_name()`), by route.
    private const NAMES_ATTRIBUTE = '_breadcrumb_names';

    /**
     * Where the pages of an app or a bundle start: the namespaces above it are
     * the bundle's, not steps of a trail.
     */
    private const PAGES_NAMESPACE = 'Controller'.ClassHelper::NAMESPACE_SEPARATOR.'Pages';

    // The controller a level of pages lands on, when it has one.
    private const HOME_CONTROLLER = 'HomeController';

    private const CONTROLLER_SUFFIX = 'Controller';

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
                'breadcrumb_name',
                [$this, 'breadcrumbName']
            ),
            new TwigFunction(
                'breadcrumb_stack',
                [$this, 'breadcrumbStack']
            ),
            new TwigFunction(
                'breadcrumb_structured_data',
                [$this, 'breadcrumbStructuredData'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'breadcrumb_render',
                function (Environment $twig, $context, array $options = []) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/breadcrumb',
                        [
                            'items' => $this->nameItems($this->normalizeItems($this->buildStackWithCurrentRoute())),
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

    /**
     * The name a level of the trail reads under — the project, the scenario
     * the page stands in — instead of the title of its page: given by the
     * layout that knows the record, for the route that lands on it.
     */
    public function breadcrumbName(string $route, ?string $label): void
    {
        $request = $this->getCurrentRequest();

        if (! $request || null === $label || '' === trim($label)) {
            return;
        }

        $names = $request->attributes->get(self::NAMES_ATTRIBUTE, []);
        $names[$route] = $label;
        $request->attributes->set(self::NAMES_ATTRIBUTE, $names);
    }

    /**
     * The trail as the search engines read it, a schema.org BreadcrumbList,
     * from the labels and addresses the component drew — once a page, the
     * trail of its layout. JSON-LD is read wherever it stands in the page;
     * a `</script>` in a label is written in hex, not closing it.
     *
     * @param list<array{name: string, url: ?string}> $items
     */
    public function breadcrumbStructuredData(array $items): string
    {
        $request = $this->getCurrentRequest();

        if (count($items) < 2 || ! $request || $request->attributes->get(self::DESCRIBED_ATTRIBUTE)) {
            return '';
        }

        $request->attributes->set(self::DESCRIBED_ATTRIBUTE, true);
        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $elements[] = array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                // The label as drawn, its HTML escaping undone.
                'name' => html_entity_decode(trim((string) $item['name']), \ENT_QUOTES | \ENT_HTML5),
                'item' => $item['url'] ?? null,
            ]);
        }

        return '<script type="application/ld+json">'.json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ], \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_HEX_TAG | \JSON_THROW_ON_ERROR).'</script>';
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
     * The pages above the current one, read from its controller's namespace:
     * the site's root first, then under `Controller\Pages` each level — a
     * record, `Project/` — as the list of its kind beside it
     * (`ProjectController`) and the record's own home (`Project/HomeController`);
     * a level with neither gives its first index (`findIndexRoute`), kept only
     * when its controller stands above the current one. Then the controller's
     * own index. Each only when the current parameters can build it.
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

        // [route, a level of the trail] — a level stands above every page in
        // it, its own siblings included; any other index only above the pages
        // its namespace holds.
        $candidates = [[$this->findRootRoute(), true]];
        $namespace = $root;

        foreach ($levels as $level) {
            $parent = $namespace;
            $namespace .= $separator.$level;
            // A level is a record and the list of its kind: the list beside
            // its folder (`ProjectController` beside `Project/`), then the
            // record's own home (`Project/HomeController`) — the projects,
            // then the project.
            $collection = $this->findControllerIndexRoute($parent.$separator.$level.self::CONTROLLER_SUFFIX);
            $home = $this->findControllerIndexRoute($namespace.$separator.self::HOME_CONTROLLER);

            if ($collection || $home) {
                $candidates[] = [$collection, true];
                $candidates[] = [$home, true];
            } else {
                $candidates[] = [AbstractController::findIndexRoute($this->router, $namespace), false];
            }
        }

        $candidates[] = [$this->findControllerIndexRoute($controller), false];

        $ancestors = [];
        $seen = [$currentRoute => true];

        foreach ($candidates as [$candidate, $isLevel]) {
            if (null === $candidate || isset($seen[$candidate]) || (! $isLevel && ! $this->standsAbove($candidate, $controller))) {
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

    // Where the whole site starts: the page at its root, the first of every
    // trail. A site served by locale has its root page under `/{_locale}/`,
    // and at `/` a redirect to it, which is no page and has no title to read.
    private function findRootRoute(): ?string
    {
        foreach ($this->router->getRouteCollection() as $name => $route) {
            if ('/' === str_replace('/{_locale}', '', $route->getPath())
                && ! str_starts_with($name, '_')
                && is_a(ClassHelper::getClassPath($route->getDefaults()['_controller'] ?? ''), AbstractLoaderController::class, true)) {
                return $name;
            }
        }

        return null;
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

    // Each level named as the page asked (`breadcrumb_name()`), where its item
    // carries no label of its own.
    private function nameItems(array $items): array
    {
        $names = $this->getCurrentRequest()?->attributes->get(self::NAMES_ATTRIBUTE, []) ?? [];

        foreach ($items as &$item) {
            $route = $item['route'] ?? null;

            if (null === $item['label'] && null !== $route && isset($names[$route])) {
                $item['label'] = $names[$route];
            }
        }

        return $items;
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
