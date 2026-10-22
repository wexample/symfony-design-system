<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * What changes, field by field, before and after: a record's history, a
 * change to confirm, a sync under review.
 */
class DiffExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'diff',
                function (
                    Environment $twig,
                    $context,
                    array $rows,
                    array $options = []
                ): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/diff',
                        [
                            // Each `{ field, before, after }`, already words.
                            'rows' => $rows,
                            // What the two sides are: « Before », « Now »; a
                            // local and a remote record.
                            'before_label' => $options['before_label'] ?? null,
                            'after_label' => $options['after_label'] ?? null,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
