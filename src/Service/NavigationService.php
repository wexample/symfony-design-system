<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * A navigation — the views of a section, written once — made ready for any of
 * the ways it is drawn: the side menu standing for the section
 * (`menu_navigation`), which shows it whole, or tabs under the header
 * (`page_tabs`), which show the level the page stands in. Whoever writes one
 * does not know which; the app chooses (`layout_section_navigation`).
 *
 *     {
 *         label: 'Acme',                        # what the section is
 *         href: '/organizations/12',            # the section itself
 *         back: { href, label },                # where it is left for
 *         actions: [...],                       # what can be done in it, tabs only
 *         items: [{
 *             label, icon, route, params,       # a view of the section
 *             match: 'accounting_bank_',        # route prefixes it stays lit on
 *             children: [...],                  # its own views, as items
 *             active, count, href,              # said outright when known better
 *         }],
 *     }
 *
 * A bare list of items is a navigation with nothing but its items.
 */
class NavigationService
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly RouteAccessService $routeAccess,
    ) {
    }

    /**
     * Every item given its address and told whether it is the page being read
     * (`current`) or leads to it (`active`: itself or one of its children); an
     * item the reader may not open is left out — a navigation shows where they
     * can go, never a door onto a 403.
     *
     * @param array<mixed> $navigation
     *
     * @return array{label: ?string, href: ?string, back: mixed, actions: mixed, items: list<array<string, mixed>>}
     */
    public function normalize(array $navigation): array
    {
        if (array_is_list($navigation)) {
            $navigation = ['items' => $navigation];
        }

        $request = $this->requestStack->getCurrentRequest();

        return [
            'label' => $navigation['label'] ?? null,
            'href' => $navigation['href'] ?? null,
            'back' => $navigation['back'] ?? null,
            'actions' => $navigation['actions'] ?? null,
            'items' => $this->normalizeItems(
                $navigation['items'] ?? [],
                $request?->attributes->get('_route'),
                $request?->attributes->get('_route_params', []) ?? []
            ),
        ];
    }

    /**
     * The level of a normalized navigation the page stands in, as tabs show
     * it: going down through the item leading to the page as long as one of
     * its children does too. Down a level, the way back leads to the section
     * above, named after it.
     *
     * @param array<string, mixed> $navigation as normalize() gives it
     *
     * @return array<string, mixed>
     */
    public static function currentLevel(array $navigation): array
    {
        foreach ($navigation['items'] as $item) {
            if (! $item['active'] || [] === $item['children']) {
                continue;
            }

            $childActive = false;
            foreach ($item['children'] as $child) {
                $childActive = $childActive || $child['active'];
            }

            if (! $childActive) {
                break;
            }

            return self::currentLevel([
                'label' => $item['label'],
                'href' => $item['href'],
                'back' => null !== $navigation['href']
                    ? ['href' => $navigation['href'], 'label' => $navigation['label']]
                    : $navigation['back'],
                'actions' => $navigation['actions'],
                'items' => $item['children'],
            ]);
        }

        return $navigation;
    }

    /**
     * Lit on a route its `match` prefixes cover — a family of pages —, or on
     * its own route with its own parameters.
     *
     * @param array<string, mixed> $item
     * @param array<string, mixed> $currentParams
     */
    public static function isCurrent(array $item, ?string $currentRoute, array $currentParams): bool
    {
        if (null === $currentRoute) {
            return false;
        }

        foreach ((array) ($item['match'] ?? []) as $prefix) {
            if (str_starts_with($currentRoute, $prefix)) {
                return true;
            }
        }

        if (($item['route'] ?? null) !== $currentRoute) {
            return false;
        }

        foreach ($item['params'] ?? [] as $name => $value) {
            if ((string) ($currentParams[$name] ?? '') !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string, mixed>             $currentParams
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeItems(array $items, ?string $currentRoute, array $currentParams): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $route = $item['route'] ?? null;
            $params = $item['params'] ?? [];

            if (null !== $route && ! $this->routeAccess->canOpen($route, $params)) {
                continue;
            }

            $children = $this->normalizeItems($item['children'] ?? [], $currentRoute, $currentParams);
            $current = $item['active'] ?? self::isCurrent($item, $currentRoute, $currentParams);
            $childActive = false;

            foreach ($children as $child) {
                $childActive = $childActive || $child['active'];
            }

            $normalized[] = [
                'params' => $params,
                'href' => $item['href'] ?? $this->router->generate($route, $params),
                'current' => $current,
                'active' => $current || $childActive,
                'children' => $children,
            ] + $item + [
                'icon' => null,
                'count' => null,
            ];
        }

        return $normalized;
    }
}
