<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A picture in its frame, drawn by the server: `media(src, alt, options)`.
 * Options: `srcset`, `sizes`, `width` and `height` (the picture's own),
 * `href`, `caption`, `loading`, `class`, `style`. A null `src` draws the
 * frame alone, its placeholder standing for the missing picture.
 */
class MediaExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'media',
                function (Environment $twig, $context, ?string $src, string $alt = '', array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/media',
                        array_merge(['loading' => 'lazy'], $options, ['src' => $src, 'alt' => $alt])
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
