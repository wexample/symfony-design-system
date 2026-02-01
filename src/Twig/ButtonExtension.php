<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class ButtonExtension extends AbstractTemplateExtension
{
    public function __construct(
        private readonly ComponentsExtension $componentsExtension,
    ) {
    }

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
                function (Environment $twig, $context, string $icon, string $label, array $options = []) {
                    $context = is_array($context) ? $context : [];
                    $renderPass = $context['render_pass'] ?? null;
                    return $this->componentsExtension->component(
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
                    $context,
                    string $icon,
                    string $label,
                    string $href,
                    array $options = []
                ) {
                    $context = is_array($context) ? $context : [];
                    $renderPass = $context['render_pass'] ?? null;
                    return $this->componentsExtension->component(
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
