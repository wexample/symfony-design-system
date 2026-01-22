<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

abstract class AbstractTemplateExtension extends AbstractExtension
{
    /**
     * Default TwigFunction options for HTML-rendering helpers.
     */
    protected const TEMPLATE_FUNCTION_OPTIONS = [
        self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
        self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
    ];

    protected function getDefaultOptions(): array
    {
        return [];
    }

    public function renderTemplate(
        Environment $twig,
        string $template,
        array $context = []
    ): string {
        return $twig->render(
            $template,
            array_merge($this->getDefaultOptions(), $context)
        );
    }
}
