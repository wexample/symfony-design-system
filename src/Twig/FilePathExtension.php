<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A file path read the way it is looked for: the name in full, the folders
 * before it in grey, cut from their start when the room runs out. Wherever a
 * path is shown — a cell, a list, a title — it is this and not the raw string.
 *
 * Given an `href` it leads to the file, and with a `target` it opens it where
 * a target button would — `file_path(path, { href, target: 'panel' })` —
 * which is how a list of files opens one in place.
 */
class FilePathExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'file_path',
                function (Environment $twig, $context, ?string $path, array $options = []): string {
                    if ($path === null || $path === '') {
                        return '';
                    }

                    $markup = $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/file-path',
                        [
                            'path' => $path,
                            'fit' => $options['fit'] ?? false,
                            'wrap' => $options['wrap'] ?? false,
                            'class' => $options['class'] ?? null,
                        ]
                    );

                    $href = $options['href'] ?? null;

                    if (! $href) {
                        return $markup;
                    }

                    // A path leading to its file: a plain link, or — given a
                    // `target`, 'panel', 'modal' or an embed's name — the page
                    // opened there, the way a target button opens it.
                    return $this->componentsExtension->component(
                        $twig,
                        is_array($context) ? ($context['render_pass'] ?? null) : null,
                        '@WexampleSymfonyDesignSystemBundle/components/button-target',
                        [
                            'icon' => null,
                            'label' => null,
                            'options' => [
                                'href' => $href,
                                'target' => $options['target'] ?? '',
                                'target_options' => $options['target_options'] ?? [],
                                'class' => 'file-path--link',
                                // A link in the run of the text, not a button.
                                'link' => true,
                            ],
                        ],
                        ['html' => $markup]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
