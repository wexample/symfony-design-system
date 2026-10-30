<?php

namespace Wexample\SymfonyDesignSystem\Service\AgentTool;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
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
        private RequestStack $requestStack,
        #[Autowire(param: 'kernel.default_locale')]
        private string $defaultLocale,
    ) {
    }

    public function getName(): string
    {
        return 'list_pages';
    }

    public function getDescription(): string
    {
        return 'Lists the pages of the application the user may open, each with its route name and its address — `navigate` opens it. A page showing one record gives its address with a {parameter} to fill in, and the parameters it needs. `query` keeps the pages whose route or address holds a text.';
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
