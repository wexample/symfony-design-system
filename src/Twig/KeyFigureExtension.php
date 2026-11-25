<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A key figure, in a card of its own: its label, its value as large as the
 * card holds, and under it the way it went or what it is measured against.
 * A row of them reads down the labels, then across the values.
 */
class KeyFigureExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'key_figure',
                function (
                    Environment $twig,
                    $context,
                    string $label,
                    string|int|float $value,
                    array $options = []
                ): string {
                    $value = (string) $value;
                    $unit = $options['unit'] ?? null;

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/key-figure',
                        [
                            'label' => $label,
                            'value' => $value,
                            // Set back after the value: the eye reads the figure, the unit once.
                            'unit' => $unit,
                            // How many characters the value and its unit take: the value is
                            // drawn as large as the card holds that many, never on two lines.
                            'chars' => max(1, mb_strlen($value) + (null === $unit ? 0 : mb_strlen($unit) + 1)),
                            // How it moved, a percent: signed, in the colour of where it went.
                            'trend' => isset($options['trend']) ? (float) $options['trend'] : null,
                            'trend_digits' => $options['trend_digits'] ?? 2,
                            // What it is measured against, when it has no trend.
                            'sub' => $options['sub'] ?? null,
                            // Where the value leads.
                            'href' => $options['href'] ?? null,
                            // A small chart at its foot, drawn by the page (chart_sparkline()).
                            'chart' => $options['chart'] ?? null,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
