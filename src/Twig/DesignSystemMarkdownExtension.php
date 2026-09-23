<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFilter;
use Wexample\SymfonyDesignSystem\Service\DesignSystemMarkdownService;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

/**
 * `markdown_ds`, the design system's twin of `markdown`: same conversion, with
 * the tables drawn by `data_table()`.
 *
 * A filter because the page's render pass lives in the template's context, and
 * a component drawn without it would reach the page without its styles and its
 * scripts.
 */
class DesignSystemMarkdownExtension extends AbstractExtension
{
    public function __construct(
        private readonly DesignSystemMarkdownService $markdown,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown_ds', $this->convert(...), [
                self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                self::FUNCTION_OPTION_NEEDS_CONTEXT => true,
            ]),
        ];
    }

    public function convert(
        array $context,
        string $markdown,
        ?string $flavor = null
    ): string {
        return $this->markdown->toHtml($markdown, $flavor, $context['render_pass'] ?? null);
    }
}
