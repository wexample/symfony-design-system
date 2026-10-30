<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class PropertiesExtension extends AbstractTemplateExtension
{
    private const MODIFIERS = [
        'bordered',
        'split',
        'compact',
        'stacked',
        'columns',
        'tiles',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'properties',
                function (
                    Environment $twig,
                    $context,
                    array $items,
                    array $options = [],
                ) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/properties',
                        [
                            'items' => $normalized = $this->normalizeItems($items),
                            'class' => $this->buildClass($options, $normalized),
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $normalized[] = [
                'key' => $item['key'] ?? null,
                'value' => $item['value'] ?? null,
                'html' => $item['html'] ?? false,
                // In columns, what no column can hold without stretching the
                // others takes the whole width instead.
                'full' => $item['full'] ?? false,
                // A value with no word to break between — a path, an address —
                // broken anywhere, filling its lines.
                'break' => $item['break'] ?? false,
                // An identifier — an id, a hash —, read as code; kept to its
                // end (`short`), copied whole (`copy`).
                'code' => $item['code'] ?? false,
                'short' => $item['short'] ?? false,
                'copy' => $item['copy'] ?? false,
            ];
        }

        return $normalized;
    }

    private function buildClass(array $options, array $items): string
    {
        $classes = ['properties'];

        foreach (self::MODIFIERS as $modifier) {
            if ($options[$modifier] ?? false) {
                $classes[] = 'properties--'.$modifier;
            }
        }

        // How many tiles, so the rows stay even: what takes a row of its own
        // is not counted.
        if ($options['tiles'] ?? false) {
            $classes[] = 'properties--tiles-'.count(array_filter($items, fn (array $item) => ! $item['full']));
        }

        if (! empty($options['class'])) {
            $classes[] = $options['class'];
        }

        return implode(' ', $classes);
    }
}
