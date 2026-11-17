<?php

declare(strict_types=1);

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Twig\BreadcrumbExtension;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * The trail read from where the page's controller stands, when no template
 * wrote one: each level of `Controller\Pages` above it gives its home, or
 * else its index, a sibling's index is no ancestor, and a page the current
 * parameters cannot build is left out.
 */
class BreadcrumbExtensionTest extends TestCase
{
    private const PAGES = 'App\\Controller\\Pages\\';

    public function testTheTrailClimbsTheNamespacesAboveThePage(): void
    {
        $stack = $this->stackFor('product_show', ['id' => 5]);

        $this->assertSame([
            ['route' => 'shop_index', 'params' => []],
            ['route' => 'product_index', 'params' => []],
            ['route' => 'product_show', 'params' => ['id' => 5]],
        ], $stack);
    }

    public function testALevelWithAHomeLandsOnIt(): void
    {
        // A project's pages: its content types listed by the router before
        // its home, a scenario's schedules under it.
        $stack = $this->stackFor('project_scenario_schedule_index', ['project' => 1, 'scenario' => 2], [
            'project_content_type_index' => new Route('/p/{project}/types', ['_controller' => self::PAGES.'Project\\ContentTypeController::index']),
            'project_home_index' => new Route('/p/{project}', ['_controller' => self::PAGES.'Project\\HomeController::index']),
            'project_scenario_schedule_index' => new Route('/p/{project}/s/{scenario}/schedules', ['_controller' => self::PAGES.'Project\\Scenario\\ScheduleController::index']),
        ]);

        $this->assertSame(['project_home_index', 'project_scenario_schedule_index'], array_column($stack, 'route'));
    }

    public function testEveryLevelGivesItsListThenItsRecord(): void
    {
        // A scenario's schedules: the site's root, the projects, the
        // project, its scenarios, the scenario, then the page.
        $stack = $this->stackFor('project_scenario_schedule_index', ['project' => 1, 'scenario' => 2], [
            'home_index' => new Route('/', ['_controller' => self::PAGES.'HomeController::index']),
            'projects_index' => new Route('/projects', ['_controller' => self::PAGES.'ProjectController::index']),
            'project_home_index' => new Route('/p/{project}', ['_controller' => self::PAGES.'Project\\HomeController::index']),
            'project_scenario_index' => new Route('/p/{project}/s', ['_controller' => self::PAGES.'Project\\ScenarioController::index']),
            'project_scenario_home_index' => new Route('/p/{project}/s/{scenario}', ['_controller' => self::PAGES.'Project\\Scenario\\HomeController::index']),
            'project_scenario_schedule_index' => new Route('/p/{project}/s/{scenario}/schedules', ['_controller' => self::PAGES.'Project\\Scenario\\ScheduleController::index']),
        ]);

        $this->assertSame([
            'home_index',
            'projects_index',
            'project_home_index',
            'project_scenario_index',
            'project_scenario_home_index',
            'project_scenario_schedule_index',
        ], array_column($stack, 'route'));
    }

    public function testASiteServedByLocaleStartsAtItsHomeNotAtTheRedirect(): void
    {
        // `/` only redirects to the reader's locale; the home is under it.
        $stack = $this->stackFor('product_show', ['_locale' => 'fr', 'id' => 5], [
            'locale_root_redirect' => new Route('/', ['_controller' => 'Vendor\\Translations\\Controller\\LocaleRedirectController::redirect']),
            'home_index' => new Route('/{_locale}/', ['_controller' => self::PAGES.'HomeController::index']),
        ]);

        $this->assertSame(['home_index', 'shop_index', 'product_index', 'product_show'], array_column($stack, 'route'));
    }

    public function testAnIndexAskingForAParameterThePageLacksIsLeftOut(): void
    {
        $stack = $this->stackFor('product_show', ['id' => 5], [
            // The shop index now needs a store the product page does not have.
            'shop_index' => new Route('/{store}/shop', ['_controller' => self::PAGES.'Shop\\ShopController::index']),
        ]);

        $this->assertSame(['product_index', 'product_show'], array_column($stack, 'route'));
    }

    public function testAPageOutsideControllerPagesHasNoAncestors(): void
    {
        $stack = $this->stackFor('api_ping', []);

        $this->assertSame([['route' => 'api_ping', 'params' => []]], $stack);
    }

    public function testATrailAppendedByHandIsKept(): void
    {
        $extension = $this->createExtension($this->routes(), 'product_show', ['id' => 5]);
        $extension->breadcrumbAppendRoute('shop_index');

        $this->assertSame(
            ['shop_index', 'product_show'],
            array_column($extension->breadcrumbStack(), 'route')
        );
    }

    public function testWithoutARequestThereIsNoTrail(): void
    {
        $extension = new BreadcrumbExtension(
            $this->createStub(ComponentsExtension::class),
            new RequestStack(),
            $this->createRouter($this->routes()),
        );

        $this->assertSame([], $extension->breadcrumbStack());
    }

    private function stackFor(string $route, array $params, array $overrides = []): array
    {
        return $this->createExtension($this->routes($overrides), $route, $params)->breadcrumbStack();
    }

    private function routes(array $overrides = []): RouteCollection
    {
        $routes = [
            // A sibling of the product controller, listed first so it is the
            // first index of the Product namespace: it must not be taken for
            // the level's ancestor.
            'category_index' => new Route('/shop/categories', ['_controller' => self::PAGES.'Shop\\Product\\CategoryController::index']),
            'shop_index' => new Route('/shop', ['_controller' => self::PAGES.'Shop\\ShopController::index']),
            'product_index' => new Route('/shop/products', ['_controller' => self::PAGES.'Shop\\Product\\ProductController::index']),
            'product_show' => new Route('/shop/products/{id}', ['_controller' => self::PAGES.'Shop\\Product\\ProductController::show']),
            'api_ping' => new Route('/api/ping', ['_controller' => 'App\\Controller\\Api\\PingController::ping']),
        ];

        $collection = new RouteCollection();

        foreach (array_merge($routes, $overrides) as $name => $route) {
            $collection->add($name, $route);
        }

        return $collection;
    }

    private function createExtension(RouteCollection $routes, string $route, array $params): BreadcrumbExtension
    {
        $request = new Request();
        $request->attributes->set('_route', $route);
        $request->attributes->set('_route_params', $params);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new BreadcrumbExtension(
            $this->createStub(ComponentsExtension::class),
            $requestStack,
            $this->createRouter($routes),
        );
    }

    private function createRouter(RouteCollection $routes): RouterInterface
    {
        return new class ($routes) implements RouterInterface {
            private RequestContext $context;

            public function __construct(private readonly RouteCollection $routes)
            {
                $this->context = new RequestContext();
            }

            public function getRouteCollection(): RouteCollection
            {
                return $this->routes;
            }

            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return (new UrlGenerator($this->routes, $this->context))->generate($name, $parameters, $referenceType);
            }

            public function match(string $pathinfo): array
            {
                return [];
            }

            public function setContext(RequestContext $context): void
            {
                $this->context = $context;
            }

            public function getContext(): RequestContext
            {
                return $this->context;
            }
        };
    }
}
