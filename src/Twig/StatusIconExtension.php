<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class StatusIconExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'status_icon',
                function (
                    Environment $twig,
                    $context,
                    string $type,
                    array $options = []
                ): string {
                    $context = is_array($context) ? $context : [];

                    return $this->componentsExtension->component(
                        $twig,
                        $context['render_pass'] ?? null,
                        '@WexampleSymfonyDesignSystemBundle/components/status-icon',
                        [
                            'type' => $type,
                            // How much of the ring is drawn, 0 to 1. Null leaves
                            // it whole, which is what every state but a running
                            // one means.
                            'progress' => $options['progress'] ?? null,
                            // Overrides what the type would put inside the ring.
                            'glyph_name' => $options['glyph'] ?? null,
                            'size' => $options['size'] ?? null,
                            // Given a label the circle is read out, without one it
                            // is decoration beside something that already says it.
                            'label' => $options['label'] ?? null,
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
            // A row of verdicts — the checks a run passed —, a status icon
            // each: `[{ type, label }]`, its label said under the pointer.
            new TwigFunction(
                'status_checks',
                function (
                    Environment $twig,
                    $context,
                    array $checks,
                    array $options = []
                ): string {
                    $context = is_array($context) ? $context : [];

                    return $this->componentsExtension->component(
                        $twig,
                        $context['render_pass'] ?? null,
                        '@WexampleSymfonyDesignSystemBundle/components/status-checks',
                        [
                            'checks' => array_map(static fn (array $check): array => [
                                'type' => $check['type'] ?? 'pending',
                                'label' => $check['label'] ?? null,
                            ], $checks),
                            // What the row is, read out: « Finale checks ».
                            'label' => $options['label'] ?? null,
                            // `sm` in a row of a table, as a line of text is tall.
                            'size' => $options['size'] ?? 'sm',
                            'class' => $options['class'] ?? null,
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
}
