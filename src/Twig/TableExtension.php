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
                'table',
                function (
                    Environment $twig,
                    array $columns,
                    array $rows,
                    array $options = [],
                ) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/partials/table.html.twig',
                        [
                            'columns' => $this->normalizeColumns($columns),
                            'rows' => $rows,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
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
