<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class BreadcrumbExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'breadcrumb',
                function (
                    Environment $twig,
                    array $items,
                    array $options = [],
                ) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/breadcrumb.html.twig',
                        [
                            'items' => $this->normalizeItems($items),
                            'options' => $options,
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
            if (is_string($item)) {
                $normalized[] = [
                    'route' => $item,
                    'params' => [],
                ];
                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $normalized[] = [
                'route' => $item['route'] ?? null,
                'params' => $item['params'] ?? [],
                'label' => $item['label'] ?? null,
                'current' => $item['current'] ?? false,
            ];
        }

        return $normalized;
    }
}
