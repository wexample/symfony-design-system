<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Whether the reader may open the page a route serves, asked before a link to
 * it is drawn — a menu entry leading to a 403 is a door painted on a wall.
 *
 * The same answer the firewall gives, read where the firewall reads it:
 * `access_control` (`security.access_map`, decided as the access listener
 * decides), then the `#[IsGranted]` of the controller — those naming no
 * subject, the only ones a link can be judged by before the page loads its
 * record.
 */
final class RouteAccessService
{
    /** @var array<string, bool> */
    private array $decided = [];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly AccessMapInterface $accessMap,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function canOpen(string $route, array $parameters = []): bool
    {
        $key = $route.'?'.http_build_query($parameters);

        return $this->decided[$key] ??= $this->decide($route, $parameters);
    }

    private function decide(string $route, array $parameters): bool
    {
        $token = $this->tokenStorage->getToken() ?? new NullToken();

        try {
            $path = $this->router->generate($route, $parameters);
        } catch (RoutingException) {
            // Not a link anyone can follow: whoever draws it will fail louder.
            return true;
        }

        $current = $this->requestStack->getCurrentRequest();
        $target = Request::create($path, server: $current?->server->all() ?? []);

        [$attributes] = $this->accessMap->getPatterns($target);

        if ($attributes && ! $this->accessDecisionManager->decide($token, $attributes, $target, null, true)) {
            return false;
        }

        foreach ($this->isGrantedAttributes($route) as $attribute) {
            if (! $this->accessDecisionManager->decide($token, [$attribute], null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The roles and voter attributes the controller asks for of anyone, its
     * class's and its method's: those with a subject, an expression or a
     * closure need the page's own arguments, and are left to the page.
     *
     * @return string[]
     */
    private function isGrantedAttributes(string $route): array
    {
        $controller = $this->router->getRouteCollection()->get($route)?->getDefault('_controller');

        if (! is_string($controller) || ! str_contains($controller, '::')) {
            return [];
        }

        [$class, $method] = explode('::', $controller, 2);

        if (! class_exists($class) || ! method_exists($class, $method)) {
            return [];
        }

        $attributes = [];
        $reflections = [new \ReflectionClass($class), new \ReflectionMethod($class, $method)];

        foreach ($reflections as $reflection) {
            foreach ($reflection->getAttributes(IsGranted::class) as $attribute) {
                $isGranted = $attribute->newInstance();

                if (null === $isGranted->subject && is_string($isGranted->attribute)
                    && (! $isGranted->methods || in_array('GET', $isGranted->methods, true))) {
                    $attributes[] = $isGranted->attribute;
                }
            }
        }

        return $attributes;
    }
}
