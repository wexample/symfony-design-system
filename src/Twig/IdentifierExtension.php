<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * An identifier — an id, a hash, a reference — read as code: `short` keeps its
 * end, the whole in the tooltip; `copy` hangs a button putting the whole in
 * the clipboard, said in a toast.
 */
class IdentifierExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'identifier',
                function (Environment $twig, $context, $value, array $options = []): string {
                    if ($value === null || $value === '') {
                        return '';
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/identifier',
                        [
                            'value' => (string) $value,
                            // True for eight characters, or how many to keep.
                            'short' => $options['short'] ?? false,
                            'copy' => $options['copy'] ?? false,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
