<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Helper\QueryHelper;
use Wexample\SymfonyDesignSystem\Helper\SortHelper;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class TableExtension extends AbstractTemplateExtension
{
    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'data_table',
                function (
                    Environment $twig,
                    $context,
                    array $columns,
                    array $rows,
                    array $options = [],
                ) {
                    $context = is_array($context) ? $context : [];
                    $columns = $this->normalizeColumns($columns);
                    $sort = $this->getSort($columns, $options);

                    // The whole list given, the table orders it itself; one
                    // page of it, the controller has ordered it already, from
                    // the same query.
                    if ($options['sort_rows'] ?? false) {
                        $rows = SortHelper::apply($rows, $sort, $this->requestStack->getCurrentRequest()?->getLocale());
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/data-table',
                        [
                            // An actions cell may render a target button, and a
                            // component cannot be registered without the pass.
                            'render_pass' => $context['render_pass'] ?? null,
                            'columns' => $this->describeSort($columns, $sort, $options),
                            'filters_clear_url' => $this->getFiltersClearUrl($options),
                            'rows' => $rows,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS + [self::FUNCTION_OPTION_NEEDS_CONTEXT => true]
            ),
        ];
    }

    private function normalizeColumns(array $columns): array
    {
        $normalized = [];

        foreach ($columns as $column) {
            if (is_string($column)) {
                $column = ['key' => $column];
            }

            $normalized[] = [
                'key' => $column['key'] ?? null,
                'label' => $column['label'] ?? null,
                'cell' => $column['cell'] ?? 'text',
                // Read by a date cell, and by nothing else: one of the format
                // names both sides of the stack answer to.
                'date_format' => $column['date_format'] ?? 'auto',
                'secondary' => $column['secondary'] ?? false,
                // Sortable only when the column says so, on a key that may not be
                // the one it shows: a date sorts on its value, a name on the last.
                'sortable' => $column['sortable'] ?? false,
                'sort_key' => $column['sort_key'] ?? $column['key'] ?? null,
                'class' => implode(' ', array_filter([
                    $column['class'] ?? null,
                    isset($column['align']) ? 'table--cell--'.$column['align'] : null,
                    // One of xs, s, m, l, xl: the column keeps it whatever its cells say.
                    isset($column['width']) ? 'table--cell--width-'.$column['width'] : null,
                ])),
            ];
        }

        return $normalized;
    }

    // The order the page's query asks for, among the keys the table allows, or
    // the default one — the same reading a controller makes with
    // SortHelper::fromQuery() before it lists the rows.
    private function getSort(array $columns, array $options): ?array
    {
        $default = $options['default_sort'] ?? null;

        return SortHelper::fromQuery(
            QueryHelper::scoped($this->requestStack->getCurrentRequest()?->query->all() ?? [], $options['query_key'] ?? null),
            SortHelper::allowedKeys($columns, $default),
            $default
        );
    }

    // Where "clear the filters" leads, while one of the table's filters holds
    // something; null otherwise — the list is then empty for real.
    private function getFiltersClearUrl(array $options): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request
            ? QueryHelper::clearUrl(
                $request->getBaseUrl().$request->getPathInfo(),
                $request->query->all(),
                array_column($options['filters'] ?? [], 'key'),
                $options['query_key'] ?? null
            )
            : null;
    }

    // What each sortable header says of the order, and the address of the next
    // one: a link, so the order works with no script at all.
    private function describeSort(array $columns, ?array $sort, array $options): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $default = $options['default_sort'] ?? null;

        foreach ($columns as &$column) {
            if (! $column['sortable']) {
                continue;
            }

            $next = SortHelper::next($sort, $column['sort_key'], $default);
            $column['sort_aria'] = SortHelper::aria($sort, $column['sort_key']);
            $column['sort_href'] = $request
                ? SortHelper::url($request->getBaseUrl().$request->getPathInfo(), $request->query->all(), $next, $default, $options['query_key'] ?? null)
                : '#';
        }

        return $columns;
    }
}
