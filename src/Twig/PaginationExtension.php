<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * The pages of a list drawn by the server: each page is an address, so each
 * entry is a link. The twin of the vue `pagination`, which emits a page
 * instead — same markup, same choice of the numbers shown.
 *
 * `pagination(page, pages_count, options)`, the page counted from 1 as an
 * address counts it. By default every link keeps the current route and its
 * query — a search, a sort — and only changes `page`:
 *
 * - `route`, `params`: another address to page through;
 * - `page_param`: the query key carrying the page (`page`);
 * - `max_visible`: how many entries the numbers may take (7, at least 5);
 * - `compact`: previous and next around "Page n / m", without the numbers.
 */
class PaginationExtension extends AbstractTemplateExtension
{
    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'pagination',
                function (Environment $twig, $context, int $page, int $pagesCount, array $options = []): string {
                    if ($pagesCount <= 1) {
                        return '';
                    }

                    $page = max(1, min($page, $pagesCount));
                    $href = $this->hrefBuilder($options);

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/pagination',
                        [
                            'page' => $page,
                            'pages_count' => $pagesCount,
                            'compact' => $options['compact'] ?? false,
                            'previous_href' => $page > 1 ? $href($page - 1) : null,
                            'next_href' => $page < $pagesCount ? $href($page + 1) : null,
                            'entries' => array_map(
                                static fn (?int $entry): ?array => null === $entry ? null : [
                                    'page' => $entry,
                                    'href' => $href($entry),
                                    'current' => $entry === $page,
                                ],
                                self::visiblePages($page, $pagesCount, $options['max_visible'] ?? 7)
                            ),
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    /**
     * The page numbers worth showing, counted from 1, with null standing for
     * the gap an ellipsis fills: the first, the last, the current one and its
     * neighbours — the same choice as the vue twin's.
     *
     * @return list<int|null>
     */
    public static function visiblePages(int $page, int $pagesCount, int $maxVisible = 7): array
    {
        $max = max(5, $maxVisible);

        if ($pagesCount <= $max) {
            return range(1, $pagesCount);
        }

        // Three slots go to the first page, the last one and one ellipsis.
        $side = intdiv($max - 3, 2);
        $wanted = [1, $pagesCount, $page];

        for ($offset = 1; $offset <= $side; ++$offset) {
            $wanted[] = $page - $offset;
            $wanted[] = $page + $offset;
        }

        $wanted = array_values(array_unique(array_filter(
            $wanted,
            static fn (int $entry): bool => $entry >= 1 && $entry <= $pagesCount
        )));
        sort($wanted);

        $output = [];
        $previous = null;

        foreach ($wanted as $entry) {
            if (null !== $previous && $entry - $previous > 1) {
                $output[] = null;
            }

            $output[] = $entry;
            $previous = $entry;
        }

        return $output;
    }

    /**
     * @return callable(int): string the address of a page
     */
    private function hrefBuilder(array $options): callable
    {
        $request = $this->requestStack->getCurrentRequest();
        $pageParam = $options['page_param'] ?? 'page';
        $route = $options['route'] ?? $request?->attributes->get('_route');
        $params = $options['params'] ?? array_merge(
            (array) $request?->attributes->get('_route_params', []),
            $request?->query->all() ?? []
        );

        return function (int $page) use ($route, $params, $pageParam): string {
            // The first page needs no parameter: its address stays the list's.
            $query = $params;
            unset($query[$pageParam]);

            if ($page > 1) {
                $query[$pageParam] = $page;
            }

            return $this->urlGenerator->generate($route, $query);
        };
    }
}
