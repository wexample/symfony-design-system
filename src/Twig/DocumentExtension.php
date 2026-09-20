<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class DocumentExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'document_embed',
                function (
                    Environment $twig,
                    $context,
                    string $src,
                    string $title,
                    array $options = [],
                ) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/document-embed',
                        [
                            'src' => $src,
                            'title' => $title,
                            'loading' => $options['loading'] ?? 'lazy',
                            'class' => $this->buildClass($options),
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    private function buildClass(array $options): string
    {
        $classes = ['media'];

        // Without a ratio the box has no height of its own, so it takes the one
        // its parent gives.
        $classes[] = empty($options['ratio'])
            ? 'media--fill'
            : 'media--'.$options['ratio'];

        if (! empty($options['class'])) {
            $classes[] = $options['class'];
        }

        return implode(' ', $classes);
    }
}
