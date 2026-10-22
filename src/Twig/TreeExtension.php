<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class TreeExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'tree',
                function (
                    Environment $twig,
                    $context,
                    array $items,
                    array $options = [],
                ) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/tree',
                        [
                            'items' => $this->normalizeItems($items),
                            'class' => implode(' ', array_filter([
                                'tree',
                                $options['class'] ?? null,
                            ])),
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    private function normalizeItems(
        array $items,
        int $depth = 0,
    ): array {
        $normalized = [];

        foreach ($items as $item) {
            $normalized[] = [
                'label' => $item['label'] ?? '',
                'icon' => $item['icon'] ?? null,
                'href' => $item['href'] ?? null,
                // The partial nests, so each row is told its own depth rather than
                // deducing it from a hierarchy it only sees one level of.
                'depth' => $depth,
                'children' => $this->normalizeItems($item['children'] ?? [], $depth + 1),
                'open' => $item['open'] ?? false,
                'selected' => $item['selected'] ?? false,
            ];
        }

        return $normalized;
    }
}
