<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;

class VueExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'vue_render_templates',
                [
                    $this,
                    'vueRenderTemplates',
                ]
            ),
        ];
    }

    public function vueRenderTemplates(): string
    {
        // Add vue js templates.
        return '';
    }
}
