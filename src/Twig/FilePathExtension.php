<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * A file path read the way it is looked for: the name in full, the folders
 * before it in grey, cut from their start when the room runs out. Wherever a
 * path is shown — a cell, a list, a title — it is this and not the raw string.
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

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/file-path',
                        [
                            'path' => $path,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
