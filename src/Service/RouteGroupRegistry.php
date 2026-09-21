<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Attribute\AbstractRouteGroup;
use Wexample\SymfonyDesignSystem\Interface\RouteGroupVoterInterface;
use Wexample\SymfonyHelpers\Helper\RouteHelper;

/**
 * Which routes asked to join which run, for every attribute that asks it.
 *
 * The router already knows every page the application can serve, bundles
 * included, so nothing has to be declared a second time in a list somewhere:
 * the collection is walked and the routes carrying the attribute are kept.
 *
 * One registry and not one per attribute: a menu item and a page section are
 * the same question — a group to join, a rank inside it — and two walks written
 * apart are two walks that drift apart.
 *
 * Read once per request and held: a menu is drawn on every page, and a walk
 * over the whole collection reflecting on each controller is not something to
 * repeat for the second menu of the same page. Nothing is written to disk —
 * the collection it reads from is itself compiled and cached, so a cache here
 * would only add a second thing to invalidate when a bundle arrives.
 */
class RouteGroupRegistry
{
    /**
     * @var array<class-string<AbstractRouteGroup>, array<string, string[]>> route names by
     *                                                                      group, by attribute, filled as each attribute is first asked for
     */
    private array $collected = [];

    /**
     * @param iterable<RouteGroupVoterInterface> $voters
     */
    public function __construct(
        private readonly RouterInterface $router,
        private readonly iterable $voters = [],
    ) {
    }

    /**
     * @param class-string<AbstractRouteGroup> $attribute
     *
     * @return string[] the group's route names, lightest weight first
     */
    public function getGroup(
        string $attribute,
        string $group
    ): array {
        $routes = $this->all($attribute)[$group] ?? [];

        // Asked here and not while collecting: what a route declared never
        // changes, where it is being asked from changes every request.
        foreach ($this->voters as $voter) {
            $routes = array_values(array_filter(
                $routes,
                static fn (string $route): bool => ! $voter->excludes($route)
            ));
        }

        return $routes;
    }

    /**
     * @param class-string<AbstractRouteGroup> $attribute
     *
     * @return array<string, string[]>
     */
    public function all(string $attribute): array
    {
        return $this->collected[$attribute] ??= $this->collect($attribute);
    }

    /**
     * @param class-string<AbstractRouteGroup> $attribute
     *
     * @return array<string, string[]>
     */
    private function collect(string $attribute): array
    {
        $found = [];

        foreach ($this->router->getRouteCollection() as $name => $route) {
            $method = RouteHelper::resolveMethodReflection($route->getDefault('_controller'));

            if (! $method) {
                continue;
            }

            foreach ($method->getAttributes($attribute) as $declaration) {
                /** @var AbstractRouteGroup $item */
                $item = $declaration->newInstance();

                $found[$item->group][] = [$item->weight, $name];
            }
        }

        $groups = [];

        foreach ($found as $group => $items) {
            // The route name breaks a tie, so two items of the same weight keep
            // an order somebody can predict instead of the one the router was
            // built in.
            usort($items, static fn (array $a, array $b): int => $a <=> $b);

            $groups[$group] = array_column($items, 1);
        }

        return $groups;
    }
}
