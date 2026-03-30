<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class ButtonExtension extends AbstractTemplateExtension
{
    public function __construct(
        private readonly ComponentsExtension $componentsExtension,
        private readonly UrlGeneratorInterface $urlGenerator,
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
                'button_menu',
                function (
                    Environment $twig,
                    $context,
                    string $icon,
                    string $label,
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
            new TwigFunction(
                'button_modal',
                function (
                    Environment $twig,
                    $context,
                    string $icon,
                    string $label,
                    string $routeName,
                    array $routeParams = [],
                    array $options = []
                ) {
                    $context = is_array($context) ? $context : [];
                    $renderPass = $context['render_pass'] ?? null;
                    $options['href'] = $this->urlGenerator->generate($routeName, $routeParams);
                    $options['modal'] = true;

                    return $this->componentsExtension->component(
                        $twig,
                        $renderPass,
                        '@WexampleSymfonyDesignSystemBundle/components/button-modal',
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
