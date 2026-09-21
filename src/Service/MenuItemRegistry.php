<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Attribute\MenuItem;
use Wexample\SymfonyHelpers\Helper\RouteHelper;

/**
 * Which routes asked to appear in a menu, group by group.
 *
 * The router already knows every page the application can serve, bundles
 * included, so nothing has to be declared a second time in a list somewhere:
 * the collection is walked and the routes carrying the attribute are kept.
 *
 * Read once per request and held: a menu is drawn on every page, and a walk
 * over the whole collection reflecting on each controller is not something to
 * repeat for the second menu of the same page. Nothing is written to disk —
 * the collection it reads from is itself compiled and cached, so a cache here
 * would only add a second thing to invalidate when a bundle arrives.
 */
class MenuItemRegistry
{
    /**
     * @var array<string, string[]>|null route names by group, null until the walk has run
     */
    private ?array $groups = null;

    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @return string[] the group's route names, lightest weight first
     */
    public function getGroup(string $group): array
    {
        return $this->all()[$group] ?? [];
    }

    /**
     * @return array<string, string[]>
     */
    public function all(): array
    {
        return $this->groups ??= $this->collect();
    }

    /**
     * @return array<string, string[]>
     */
    private function collect(): array
    {
        $found = [];

        foreach ($this->router->getRouteCollection() as $name => $route) {
            $method = RouteHelper::resolveMethodReflection($route->getDefault('_controller'));

            if (! $method) {
                continue;
            }

            foreach ($method->getAttributes(MenuItem::class) as $attribute) {
                /** @var MenuItem $item */
                $item = $attribute->newInstance();

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
