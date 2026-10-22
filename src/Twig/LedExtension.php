<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A lit dot saying a state at a glance, in a line of text: ok, running, in
 * error, off. Smaller than a marker, quieter than a status circle: what a
 * summary or a list of services shows by the dozen.
 */
class LedExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'led',
                function (
                    Environment $twig,
                    $context,
                    string $tone,
                    ?string $label = null,
                    array $options = []
                ): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/led',
                        [
                            // A state (`success`, `info`, `warning`, `error`,
                            // `running`, `dev`), `off`, or a category (`cat-mint`).
                            'tone' => $tone,
                            // The words beside the dot; none, the dot alone,
                            // said aloud through `title`.
                            'label' => $label,
                            // Breathing: something under way. On by default
                            // for `running`.
                            'pulse' => $options['pulse'] ?? 'running' === $tone,
                            'title' => $options['title'] ?? null,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
