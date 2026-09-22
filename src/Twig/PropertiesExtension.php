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
                            'items' => $this->normalizeItems($items),
                            'class' => $this->buildClass($options),
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
            ];
        }

        return $normalized;
    }

    private function buildClass(array $options): string
    {
        $classes = ['properties'];

        foreach (self::MODIFIERS as $modifier) {
            if ($options[$modifier] ?? false) {
                $classes[] = 'properties--'.$modifier;
            }
        }

        if (! empty($options['class'])) {
            $classes[] = $options['class'];
        }

        return implode(' ', $classes);
    }
}
