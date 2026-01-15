<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

abstract class AbstractTemplateExtension extends AbstractExtension
{
    abstract protected function getFunctionName(): string;

    abstract protected function getTemplatePath(): string;

    protected function getDefaultOptions(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                $this->getFunctionName(),
                [
                    $this,
                    'renderTemplate',
                ],
                [
                    self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            ),
        ];
    }

    public function renderTemplate(
        Environment $twig,
        string $icon,
        string $label,
        array $options = []
    ): string {
        return $twig->render(
            $this->getTemplatePath(),
            array_merge($this->getDefaultOptions(), [
                'icon' => $icon,
                'label' => $label,
                'options' => $options,
            ])
        );
    }
}
