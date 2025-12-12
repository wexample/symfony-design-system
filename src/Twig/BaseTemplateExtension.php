<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFunction;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class BaseTemplateExtension extends AbstractExtension
{
    final public const DEFAULT_LAYOUT_TITLE_TRANSLATION_KEY = '@page::page_title';
    final public const DEFAULT_APP_TITLE_TRANSLATION_KEY = 'front.app.global::name';

    public function __construct(
        protected TranslatorInterface $translator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'base_template_render_title',
                [
                    $this,
                    'baseTemplateRenderTitle',
                ]
            ),
        ];
    }

    public function baseTemplateRenderTitle(
        ?string $layoutTitle = null,
        array $layoutTitleParameters = [],
        ?string $appTitle = null,
    ): string {
        $resolvedLayoutTitle = $layoutTitle ?: $this->translator->trans(
            self::DEFAULT_LAYOUT_TITLE_TRANSLATION_KEY,
            $layoutTitleParameters
        );

        $resolvedAppTitle = $appTitle ?: $this->translator->trans(
            self::DEFAULT_APP_TITLE_TRANSLATION_KEY
        );

        $parts = array_filter(
            [
                $resolvedLayoutTitle,
                $resolvedAppTitle,
            ],
            static fn (?string $value): bool => null !== $value && '' !== trim($value)
        );

        return implode(' | ', $parts);
    }
}
