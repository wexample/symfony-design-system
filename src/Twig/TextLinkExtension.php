<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A link inside running text. `external: true` opens it in a new window with
 * `rel="noopener noreferrer"`, so no page has to spell those out by hand.
 */
class TextLinkExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'text_link',
                function (Environment $twig, $context, string $href, string $label, array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/text-link',
                        [
                            'href' => $href,
                            'label' => $label,
                            'external' => $options['external'] ?? false,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
