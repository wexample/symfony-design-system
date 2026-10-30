<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Lines one under the other, each led by a mark — an icon of the design
 * system, an emoji, a character —, a tone colouring the mark or the line.
 */
class ItemListExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'item_list',
                function (Environment $twig, $context, array $items, array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/item-list',
                        [
                            // [{ label, icon, tone }].
                            'items' => $items,
                            // What a tone colours: 'mark', or 'line'.
                            'toned' => $options['toned'] ?? 'mark',
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
