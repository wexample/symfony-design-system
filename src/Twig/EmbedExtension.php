<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A room of the page that another page is loaded into. What the browser needs
 * — its name, the page it opens on, whether that waits to be seen — goes to the
 * script; what is drawn — the close button, the spinner — to the template.
 *
 * `src` is the page it opens on; `lazy` loads it only once the embed is on
 * screen, which is what a wall of them wants. `closable` gives it a button that
 * empties it and hides the zone holding it, until something loads it again.
 */
class EmbedExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'page_embed',
                function (Environment $twig, $context, string $name, array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/embed',
                        [
                            'closable' => $options['closable'] ?? false,
                            'page_loading' => $options['page_loading'] ?? true,
                        ],
                        [
                            'name' => $name,
                            'src' => $options['src'] ?? null,
                            'lazy' => $options['lazy'] ?? false,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
