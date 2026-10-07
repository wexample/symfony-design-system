<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Wexample\SymfonyDesignSystem\Service\RouteAccessService;

/**
 * Whether a link is drawn: the firewall's answer, read before the page loads —
 * `access_control`, then the controller's `#[IsGranted]` naming no subject.
 */
class RouteAccessServiceTest extends TestCase
{
    public function testWithoutSecurityEveryRouteIsOpen(): void
    {
        $service = new RouteAccessService($this->router(), new RequestStack());

        $this->assertTrue($service->canOpen('admin'));
    }

    public function testAccessControlDecidesFirst(): void
    {
        $service = $this->service(granted: ['ROLE_USER'], accessControl: ['/admin' => ['ROLE_ADMIN']]);

        $this->assertFalse($service->canOpen('admin'));
        $this->assertTrue($service->canOpen('open'));
    }

    public function testTheControllerIsGrantedCounts(): void
    {
        $this->assertFalse($this->service(granted: [])->canOpen('guarded'));
        $this->assertTrue($this->service(granted: ['ROLE_EDITOR'])->canOpen('guarded'));
    }

    public function testAnIsGrantedWithASubjectIsLeftToThePage(): void
    {
        $this->assertTrue($this->service(granted: [])->canOpen('subject'));
    }

    private function service(array $granted, array $accessControl = []): RouteAccessService
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturnCallback(
            fn (Request $request) => [$accessControl[$request->getPathInfo()] ?? null, null]
        );

        $decision = $this->createStub(AccessDecisionManagerInterface::class);
        $decision->method('decide')->willReturnCallback(
            fn (TokenInterface $token, array $attributes) => [] !== array_intersect($attributes, $granted)
        );

        return new RouteAccessService($this->router(), new RequestStack(), $accessMap, $decision);
    }

    private function router(): RouterInterface
    {
        $routes = new RouteCollection();
        $routes->add('admin', new Route('/admin', ['_controller' => RouteAccessServiceTestController::class.'::open']));
        $routes->add('open', new Route('/open', ['_controller' => RouteAccessServiceTestController::class.'::open']));
        $routes->add('guarded', new Route('/guarded', ['_controller' => RouteAccessServiceTestController::class.'::guarded']));
        $routes->add('subject', new Route('/subject', ['_controller' => RouteAccessServiceTestController::class.'::subject']));

        $router = $this->createStub(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn($routes);
        $router->method('generate')->willReturnCallback(fn (string $name) => $routes->get($name)->getPath());

        return $router;
    }
}

class RouteAccessServiceTestController
{
    public function open(): void
    {
    }

    #[IsGranted('ROLE_EDITOR')]
    public function guarded(): void
    {
    }

    #[IsGranted('ROLE_EDITOR', subject: 'record')]
    public function subject(): void
    {
    }
}
