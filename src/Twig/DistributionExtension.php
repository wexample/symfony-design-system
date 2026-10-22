<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class DistributionExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'distribution',
                function (
                    Environment $twig,
                    $context,
                    array $segments,
                    array $options = []
                ): string {
                    $context = is_array($context) ? $context : [];
                    $parts = self::normalizeSegments($segments);
                    $sum = array_sum(array_column($parts, 'value'));
                    // A total below what the parts add up to is not a total but
                    // a mistake, and the parts are the thing that was counted.
                    $total = max((float) ($options['total'] ?? $sum), $sum);

                    foreach ($parts as &$part) {
                        $part['percent'] = $total > 0
                            ? round($part['value'] / $total * 100, 1)
                            : 0;

                        $part['title'] ??= self::buildTitle($part);
                    }

                    return $this->componentsExtension->component(
                        $twig,
                        $context['render_pass'] ?? null,
                        '@WexampleSymfonyDesignSystemBundle/components/distribution',
                        [
                            'parts' => $parts,
                            'total' => $total,
                            // What the total counts and no part accounts for:
                            // the track shows through for exactly that much.
                            'rest' => max(0, $total - $sum),
                            'label' => $options['label'] ?? null,
                            'show_value' => $options['show_value'] ?? false,
                            'legend' => $options['legend'] ?? true,
                            'compact' => $options['compact'] ?? false,
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
     * A segment is what it is worth, what it is called, which state it is, and
     * where it leads. Everything but the value is optional: a part with no
     * state is the neutral one, and one with no target is read and not clicked.
     */
    private static function normalizeSegments(array $segments): array
    {
        $parts = [];

        foreach ($segments as $segment) {
            if (! is_array($segment)) {
                continue;
            }

            $parts[] = [
                'value' => max(0, (float) ($segment['value'] ?? 0)),
                'label' => $segment['label'] ?? null,
                'type' => $segment['type'] ?? 'skipped',
                'href' => $segment['href'] ?? null,
                'title' => $segment['title'] ?? null,
            ];
        }

        return $parts;
    }

    /**
     * What a part says under the pointer, and to a reader who cannot see the
     * bar: what it is, how many, and what share of the whole.
     */
    private static function buildTitle(array $part): string
    {
        $count = $part['value'].' ('.$part['percent'].'%)';

        return $part['label'] === null
            ? $count
            : $part['label'].' — '.$count;
    }
}
