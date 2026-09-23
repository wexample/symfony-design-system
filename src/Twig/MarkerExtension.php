<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * The capsule that says one thing about what stands beside it.
 *
 * A state, a name, a version, a category: the tone is the whole of what tells
 * them apart, so one function draws all four. A state also has a glyph, which
 * `StatusExtension` holds — the circle reads the same map, and a state drawn
 * as a check in one place and a tick in another is a state nobody recognises
 * twice.
 */
class MarkerExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'marker',
                function (
                    Environment $twig,
                    $context,
                    string $tone,
                    string|int|float|null $label = null,
                    array $options = []
                ): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/marker',
                        [
                            // A state (`success`, `running`…), the neutral one
                            // for a name or a version, or a category
                            // (`cat-3`). What the capsule says is the tone;
                            // the rest is what it says it about.
                            'tone' => $tone,
                            // In words. Nothing given, the glyph is the whole
                            // of it.
                            'label' => $label,
                            // How many of it there are, which qualifies what
                            // the marker says rather than being it.
                            'count' => $options['count'] ?? null,
                            // Read by the dozen, in a cell: gives up the room
                            // it takes standing alone in a sentence.
                            'compact' => $options['compact'] ?? false,
                            // Overrides what a state would put inside; the
                            // only way a tone with no glyph of its own gets
                            // one.
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
