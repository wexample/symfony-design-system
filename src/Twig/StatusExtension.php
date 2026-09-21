<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * The words a state is drawn from, and the capsule that carries one.
 *
 * The glyph map lives here rather than in a template because two components
 * read it — the capsule and the circle — and a state drawn as a check in one
 * place and a tick in another is a state nobody recognises twice.
 */
class StatusExtension extends AbstractTemplateExtension
{
    /**
     * What stands inside a state. `running` has none: its ring is the drawing,
     * and the capsule, having no ring, names it instead.
     */
    private const GLYPHS = [
        'success' => 'ph:bold/check',
        'error' => 'ph:bold/x',
        'warning' => 'ph:bold/warning',
        'info' => 'ph:bold/info',
        'running' => null,
        'pending' => 'ph:bold/clock',
        'paused' => 'ph:bold/pause',
        'play' => 'ph:bold/play',
        'skipped' => 'ph:bold/caret-double-right',
        'disabled' => 'ph:bold/minus',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'status_glyph',
                fn (string $type): ?string => self::GLYPHS[$type] ?? null
            ),
            new TwigFunction(
                'status',
                function (
                    Environment $twig,
                    $context,
                    string $type,
                    string|int|float|null $label = null,
                    array $options = []
                ): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/status',
                        [
                            'type' => $type,
                            // What the state is, in words. Nothing given, the
                            // capsule is the state alone.
                            'label' => $label,
                            // How many of it there are, which qualifies the
                            // state rather than being it — so it is drawn a
                            // step back from the rest.
                            'count' => $options['count'] ?? null,
                            // Read by the dozen, in a cell: gives up the room
                            // it takes standing alone in a sentence.
                            'compact' => $options['compact'] ?? false,
                            // Overrides what the type would put inside.
                            'glyph_name' => $options['glyph'] ?? null,
                            // Said out loud where the glyph is all there is.
                            'title' => $options['title'] ?? null,
                            'class' => $options['class'] ?? null,
                            'attr' => $options['attr'] ?? [],
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
