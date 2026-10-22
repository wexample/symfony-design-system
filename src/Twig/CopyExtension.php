<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A button putting a text in the clipboard: a value given, or what an element
 * of the page holds. Says it copied, under the pointer and to whoever reads
 * the page aloud, and says so only once the clipboard took it.
 */
class CopyExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'copy_button',
                function (
                    Environment $twig,
                    $context,
                    array $options = []
                ): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/copy-button',
                        [
                            // The text itself — a key, a token, an address.
                            'value' => $options['value'] ?? null,
                            // Or the element whose text is copied, by its id:
                            // a block of code, read as it is when clicked.
                            'target' => $options['target'] ?? null,
                            // The word beside the icon; the icon alone, where
                            // the button sits on what it copies.
                            'label' => $options['label'] ?? false,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
