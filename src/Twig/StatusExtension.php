<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;

/**
 * The words a state is drawn from.
 *
 * The glyph map lives here rather than in a template because two components
 * read it — the marker and the circle — and a state drawn as a check in one
 * place and a tick in another is a state nobody recognises twice.
 */
class StatusExtension extends AbstractTemplateExtension
{
    /**
     * What stands inside a state. `running` has none: its ring is the drawing,
     * and the marker, having no ring, names it instead.
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
        ];
    }
}
