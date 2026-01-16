<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class ButtonExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'button',
                function (Environment $twig, string $icon, string $label, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/button.html.twig',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'button_link',
                function (Environment $twig, string $icon, string $label, string $href, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/button-link.html.twig',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'href' => $href,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
