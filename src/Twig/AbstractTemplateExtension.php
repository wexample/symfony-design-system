<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

abstract class AbstractTemplateExtension extends AbstractExtension
{
    /**
     * Return function name => template path map.
     */
    abstract protected function getTemplateMap(): array;

    protected function getDefaultOptions(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        $functions = [];

        foreach ($this->getTemplateMap() as $name => $template) {
            $functions[] = new TwigFunction(
                $name,
                fn (Environment $twig, string $icon, string $label, array $options = []) =>
                    $this->renderTemplate($twig, $template, $icon, $label, $options),
                [
                    self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            );
        }

        return $functions;
    }

    public function renderTemplate(
        Environment $twig,
        string $template,
        string $icon,
        string $label,
        array $options = []
    ): string {
        return $twig->render(
            $template,
            array_merge($this->getDefaultOptions(), [
                'icon' => $icon,
                'label' => $label,
                'options' => $options,
            ])
        );
    }
}
