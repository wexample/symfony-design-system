<?php

namespace Wexample\SymfonyDesignSystem\Service\AgentTool;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Service\RouteAccessService;
use Wexample\SymfonyLoader\Interface\AgentBackToolInterface;

/**
 * The pages of the application the user may open: the routes a person
 * reads — answered to GET, not under `/_` nor `/api/`, which programs reach —
 * that the firewall lets them through, as a menu decides which entries to
 * draw (RouteAccessService). A page needing a record to show — `/model/{id}` —
 * says what it needs, and is opened from the list it is reached by.
 */
final readonly class ListPagesAgentTool implements AgentBackToolInterface
{
    private const int LIMIT = 100;

    public function __construct(
        private RouterInterface $router,
        private RouteAccessService $routeAccess,
    ) {
    }

    public function getName(): string
    {
        return 'list_pages';
    }

    public function getDescription(): string
    {
        return 'Lists the pages of the application the user may open, each with its route name, its address and the parameters it needs, if any. `query` keeps the pages whose route or address holds a text.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Only the pages whose route name or address holds this text: "model", "account".'],
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
            if (! $this->isPage($route)) {
                continue;
            }

            if ('' !== $needle && ! str_contains(mb_strtolower($name.' '.$route->getPath()), $needle)) {
                continue;
            }

            $required = $this->requiredParameters($route);

            if (! $required && ! $this->routeAccess->canOpen($name)) {
                continue;
            }

            $pages[] = [
                'route' => $name,
                'path' => $route->getPath(),
                ...($required ? ['parameters' => $required] : []),
            ];

            if (count($pages) >= self::LIMIT) {
                break;
            }
        }

        return ['count' => count($pages), 'pages' => $pages];
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
