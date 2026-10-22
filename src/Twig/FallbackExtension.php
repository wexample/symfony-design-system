<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * The other way, when the first one fails: under a code to scan, the key to
 * type; under a link sent, the address to paste. A question naming what may
 * have gone wrong, then what to do instead — a value to copy, or a link.
 */
class FallbackExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'fallback',
                function (
                    Environment $twig,
                    $context,
                    string $question,
                    string $value,
                    array $options = []
                ): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/fallback',
                        [
                            // What may have gone wrong, asked: « Cannot scan? ».
                            'question' => $question,
                            // What to do instead: a value to type, or the words
                            // of a link when `href` is given.
                            'value' => $value,
                            // Where the other way leads, when it is a page and
                            // not a value.
                            'href' => $options['href'] ?? null,
                            // A value comes with a button copying it, unless
                            // it is short enough to type at a glance.
                            'copy' => $options['copy'] ?? true,
                            // Under something centred — a code to scan —, centred
                            // as well.
                            'center' => $options['center'] ?? false,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
