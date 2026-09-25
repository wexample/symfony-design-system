<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * One folder drawn by the server: `file_explorer(items, path, { view })`,
 * items and path as the component describes them. The vue twin is the
 * interactive one.
 */
class FileExplorerExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'file_explorer',
                function (Environment $twig, $context, array $items, array $path = [], array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/file-explorer',
                        [
                            'items' => $items,
                            'path' => $path,
                            'view' => $options['view'] ?? 'list',
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
