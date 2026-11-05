<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Helper\DiffTextHelper;

/**
 * What changed between two versions of a text, line by line
 * (`diff_text(before, after, options)`): a template, a prompt, a content
 * read from its history. The rows are DiffTextHelper's, the vue's twin.
 */
class DiffTextExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'diff_text',
                function (Environment $twig, $context, ?string $before, ?string $after, array $options = []): string {
                    $rows = DiffTextHelper::rows($before, $after, (int) ($options['context'] ?? 3), (bool) ($options['words'] ?? true));
                    $split = 'split' === ($options['mode'] ?? 'unified');

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/diff-text',
                        [
                            'rows' => $rows,
                            // Side by side, a removal facing the line that replaced it.
                            'pairs' => $split ? DiffTextHelper::sideBySide($rows) : null,
                            'stats' => DiffTextHelper::stats($rows),
                            'mode' => $split ? 'split' : 'unified',
                            // What the two texts are: « Version 3 », « Draft ».
                            'before_label' => $options['before_label'] ?? null,
                            'after_label' => $options['after_label'] ?? null,
                            'class' => $options['class'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
