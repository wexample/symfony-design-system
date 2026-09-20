<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

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
                'button_menu',
                function (
                    Environment $twig,
                    $context,
                    // A menu button standing in a bar often has only one of the
                    // two: an icon where the shape is enough, a word where the
                    // menu says what is being looked at.
                    ?string $icon,
                    ?string $label,
                    array $items = [],
                    array $options = []
                ) {
                    $context = is_array($context) ? $context : [];
                    $renderPass = $context['render_pass'] ?? null;

                    return $this->componentsExtension->component(
                        $twig,
                        $renderPass,
                        '@WexampleSymfonyDesignSystemBundle/components/button-menu',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'items' => $items,
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
            // Same signature as button_link, plus where the page it points at is
            // loaded: 'modal', 'panel', or the name of an embed the page holds.
            new TwigFunction(
                'button_target',
                function (
                    Environment $twig,
                    $context,
                    string $icon,
                    string $label,
                    string $href,
                    string $target,
                    array $options = []
                ) {
                    $context = is_array($context) ? $context : [];
                    $options['href'] = $href;
                    $options['target'] = $target;

                    return $this->componentsExtension->component(
                        $twig,
                        $context['render_pass'] ?? null,
                        '@WexampleSymfonyDesignSystemBundle/components/button-target',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'options' => $options,
                        ]
                    );
                },
                $options
            ),
        ];
    }
}
