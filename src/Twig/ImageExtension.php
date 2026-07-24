<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class ImageExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'content_image',
                function (Environment $twig, string $src, string $alt, array $options = []): string {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/partials/content-image.html.twig',
                        array_merge(['loading' => 'lazy'], $options, ['src' => $src, 'alt' => $alt])
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
