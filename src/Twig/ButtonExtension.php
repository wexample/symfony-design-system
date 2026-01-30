<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class ButtonExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        $options = [
            self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
            self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
            self::FUNCTION_OPTION_NEEDS_CONTEXT => true,
        ];

        return [
            new TwigFunction(
                'button',
                function (Environment $twig, array $context, string $icon, string $label, array $options = []) {
                    $renderPass = $context['render_pass'] ?? null;
                    $component = $twig->getFunction('component')->getCallable();

                    return $component(
                        $twig,
                        $renderPass,
                        '@WexampleSymfonyDesignSystemBundle/components/button',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'options' => $options,
                        ]
                    );
                },
                $options
            ),
            new TwigFunction(
                'button_link',
                function (
                    Environment $twig,
                    array $context,
                    string $icon,
                    string $label,
                    string $href,
                    array $options = []
                ) {
                    $renderPass = $context['render_pass'] ?? null;
                    $component = $twig->getFunction('component')->getCallable();

                    return $component(
                        $twig,
                        $renderPass,
                        '@WexampleSymfonyDesignSystemBundle/components/button-link',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'href' => $href,
                            'options' => $options,
                        ]
                    );
                },
                $options
            ),
        ];
    }
}
