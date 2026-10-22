<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class ProgressExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'progress',
                function (
                    Environment $twig,
                    $context,
                    float|int|string $current = 0,
                    array $options = []
                ): string {
                    $context = is_array($context) ? $context : [];
                    $total = (float) ($options['total'] ?? 100);
                    $current = self::normalizeValue($total, $current);

                    return $this->componentsExtension->component(
                        $twig,
                        $context['render_pass'] ?? null,
                        '@WexampleSymfonyDesignSystemBundle/components/progress-bar',
                        [
                            'current' => $current,
                            'total' => $total,
                            // What the stylesheet fills the bar with, between 0
                            // and 1, and what the vue twin computes the same way.
                            'ratio' => $total > 0 ? round($current / $total, 4) : 0,
                            'percent' => $total > 0 ? (int) round($current / $total * 100) : 0,
                            'label' => $options['label'] ?? null,
                            'show_value' => $options['show_value'] ?? false,
                            // 'percent' or 'count': how the shown value reads,
                            // a share or the count against its total.
                            'value_format' => $options['value_format'] ?? 'percent',
                            // Work whose end is not known: the bar sweeps rather
                            // than claiming a share.
                            'indeterminate' => $options['indeterminate'] ?? false,
                            'type' => $options['type'] ?? 'info',
                            'size' => $options['size'] ?? null,
                            'class' => $options['class'] ?? null,
                            'attr' => $options['attr'] ?? [],
                        ]
                    );
                },
                [
                    self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                    self::FUNCTION_OPTION_NEEDS_CONTEXT => true,
                ]
            ),
        ];
    }

    /**
     * Accepts a count or a share written as '54%', and never answers outside the
     * bounds — the same reading the cli progress does, so a value written for one
     * means the same to the other.
     */
    public static function normalizeValue(
        float $total,
        float|int|string $current
    ): float {
        if (is_string($current)) {
            $current = str_ends_with(trim($current), '%')
                ? (float) trim($current, " \t\n\r\0\x0B%") / 100 * $total
                : (float) $current;
        }

        return max(0, min($total, (float) $current));
    }
}
