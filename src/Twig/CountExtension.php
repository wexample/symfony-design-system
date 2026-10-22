<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A count as a pill: how many wait to be seen — unread, in a cart —, on the
 * corner of an icon or in a line. Red when it hangs on a corner, as such
 * counts are; the tone of the row otherwise.
 */
class CountExtension extends AbstractTemplateExtension
{
    public const int DEFAULT_MAX = 99;

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'count_badge',
                function (
                    Environment $twig,
                    $context,
                    int|null $value,
                    array $options = []
                ): string {
                    $corner = $options['corner'] ?? false;

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/count-badge',
                        [
                            'value' => $value,
                            // Past it, `99+`: the figure stops growing where
                            // its exact value stops mattering.
                            'max' => $options['max'] ?? self::DEFAULT_MAX,
                            // A word in place of the figure: « New ».
                            'label' => $options['label'] ?? null,
                            // A dot alone: there is something, how much does not say.
                            'dot' => $options['dot'] ?? false,
                            'corner' => $corner,
                            // `error` (the default on a corner), `accent`, `info`,
                            // `success`, `warning`; none, the row's own.
                            'tone' => $options['tone'] ?? ($corner ? 'error' : null),
                            'show_zero' => $options['show_zero'] ?? false,
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
