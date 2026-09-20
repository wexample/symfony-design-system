<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class TableExtension extends AbstractTemplateExtension
{
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

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/data-table',
                        [
                            // An actions cell may render a target button, and a
                            // component cannot be registered without the pass.
                            'render_pass' => $context['render_pass'] ?? null,
                            'columns' => $this->normalizeColumns($columns),
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
                'class' => implode(' ', array_filter([
                    $column['class'] ?? null,
                    isset($column['align']) ? 'table--cell--'.$column['align'] : null,
                ])),
            ];
        }

        return $normalized;
    }
}
