<?php

namespace Wexample\SymfonyDesignSystem\Service\AgentTool;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Service\RouteAccessService;
use Psr\Log\LoggerInterface;
use Wexample\SymfonyHelpers\Attribute\RouteDescription;
use Wexample\SymfonyLoader\Attribute\AgentRoute;
use Wexample\SymfonyLoader\Interface\AgentBackToolInterface;

/**
 * The pages offered to the agents (`#[AgentRoute]` on their controller,
 * described by `#[RouteDescription]`) that the user may open: answered to GET, and let through by the firewall as a
 * menu decides which entries to draw (RouteAccessService). A page needing a
 * record to show — `/model/{id}` — says what it needs.
 */
final readonly class ListPagesAgentTool implements AgentBackToolInterface
{
    private const int LIMIT = 100;

    public function __construct(
        private RouterInterface $router,
        private RouteAccessService $routeAccess,
        private RequestStack $requestStack,
        #[Autowire(param: 'kernel.default_locale')]
        private string $defaultLocale,
        private LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'list_pages';
    }

    public function getDescription(): string
    {
        return 'Lists the pages of the application the user may open, each with what it is for, its route name and its address — `navigate` opens it. A page showing one record gives its address with a {parameter} to fill in, and the parameters it needs. `query` keeps the pages whose description, route or address holds a text.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Only the pages whose description, route name or address holds this text: "model", "account".'],
            ],
        ];
    }

    public function getAnnotations(): array
    {
        return ['readOnlyHint' => true];
    }

    public function execute(array $arguments): mixed
    {
        $needle = is_string($arguments['query'] ?? null) ? mb_strtolower(trim($arguments['query'])) : '';
        $pages = [];

        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            $description = $this->isPage($route) ? $this->offeredDescription($name, $route) : null;

            if (null === $description) {
                continue;
            }

            if ('' !== $needle && ! str_contains(mb_strtolower($description.' '.$name.' '.$route->getPath()), $needle)) {
                continue;
            }

            $required = $this->requiredParameters($route);

            if (! $required && ! $this->routeAccess->canOpen($name)) {
                continue;
            }

            $pages[] = [
                'description' => $description,
                'route' => $name,
                'url' => $this->urlOf($name, $route, $required),
                ...($required ? ['parameters' => $required] : []),
            ];

            if (count($pages) >= self::LIMIT) {
                break;
            }
        }

        return ['count' => count($pages), 'pages' => $pages];
    }

    /**
     * The address as the user's browser opens it — its locale and defaults
     * filled in by the router —; a page needing a record keeps its
     * placeholders for them, `{id}`, its other parameters filled in.
     *
     * @param string[] $required
     */
    private function urlOf(string $name, Route $route, array $required): string
    {
        $placeholders = array_combine($required, array_map(static fn (string $parameter): string => '__'.$parameter.'__', $required));

        try {
            $url = $this->router->generate($name, $placeholders);
        } catch (\Throwable) {
            // A parameter whose format the placeholder does not match: the
            // path itself, in the user's language.
            $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? $this->defaultLocale;

            return str_replace('{_locale}', $locale, $route->getPath());
        }

        foreach ($required as $parameter) {
            $url = str_replace('__'.$parameter.'__', '{'.$parameter.'}', $url);
        }

        return $url;
    }

    /**
     * What a page offered to the agents is for — its method or its class
     * offering it —; null for a page not offered, or offered without saying
     * what it is for, which the log reports.
     */
    private function offeredDescription(string $name, Route $route): ?string
    {
        $controller = $route->getDefault('_controller');

        if (! is_string($controller) || ! str_contains($controller, '::')) {
            return null;
        }

        [$class, $method] = explode('::', $controller, 2);

        if (! class_exists($class) || ! method_exists($class, $method)) {
            return null;
        }

        $reflection = new \ReflectionMethod($class, $method);

        if (! $reflection->getAttributes(AgentRoute::class) && ! (new \ReflectionClass($class))->getAttributes(AgentRoute::class)) {
            return null;
        }

        $described = $reflection->getAttributes(RouteDescription::class)[0] ?? null;

        if (! $described) {
            $this->logger->warning(sprintf('The page "%s" is offered to agents (#[AgentRoute]) without saying what it is for (#[RouteDescription]): it is left out of list_pages.', $name));

            return null;
        }

        return $described->newInstance()->description;
    }

    private function isPage(Route $route): bool
    {
        $path = $route->getPath();

        return ($route->getMethods() === [] || in_array('GET', $route->getMethods(), true))
            && ! str_starts_with($path, '/_')
            && ! str_starts_with($path, '/api/')
            && '/api' !== $path;
    }

    /**
     * @return string[]
     */
    private function requiredParameters(Route $route): array
    {
        return array_values(array_filter(
            $route->compile()->getPathVariables(),
            static fn (string $variable): bool => ! $route->hasDefault($variable) && '_locale' !== $variable
        ));
    }
}
