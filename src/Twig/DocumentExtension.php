<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class DocumentExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            // Options: `ratio` ('16-9', '4-3'…) or the parent's height without
            // one, `loading`, `class`, and `actions` — the application's own,
            // none given by default: [{ icon, label, href, target?, download?,
            // new_window? }].
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
                            'frame_class' => $this->buildFrameClass($options),
                            'actions' => $options['actions'] ?? [],
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    private function buildClass(array $options): string
    {
        $classes = ['document-embed'];

        // Without a ratio the box has no height of its own, so it takes the one
        // its parent gives.
        if (empty($options['ratio'])) {
            $classes[] = 'document-embed--fill';
        }

        if (! empty($options['class'])) {
            $classes[] = $options['class'];
        }

        return implode(' ', $classes);
    }

    private function buildFrameClass(array $options): string
    {
        return empty($options['ratio'])
            ? 'media media--fill'
            : 'media media--'.$options['ratio'];
    }
}
