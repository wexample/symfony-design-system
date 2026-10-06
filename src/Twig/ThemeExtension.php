<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Rendering\RenderPass;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * The visitor's choice among the app's themes (`wexample_symfony_loader.themes`):
 * named sets of skin, palette, fonts and density, applied together. Light or
 * dark is not one of them — the colour scheme switch stays beside it.
 */
class ThemeExtension extends AbstractTemplateExtension
{
    /**
     * @param array<string, array<string, string>> $themes
     */
    public function __construct(
        ComponentsExtension $componentsExtension,
        #[Autowire(param: 'loader.themes')]
        private readonly array $themes,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'theme_select',
                function (Environment $twig, $context): string {
                    // One theme is no choice: nothing to offer.
                    if (count($this->themes) < 2) {
                        return '';
                    }

                    $renderPass = $context['render_pass'] ?? null;
                    $usages = $renderPass instanceof RenderPass ? $renderPass->usages : [];

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/theme-select',
                        [
                            'themes' => $this->themes,
                            'current' => $this->current($usages),
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    /**
     * The theme whose every axis is the one in force; none when the visitor
     * set an axis apart (the developer's toolbar).
     *
     * @param array<string, string|null> $usages
     */
    private function current(array $usages): ?string
    {
        foreach ($this->themes as $name => $axes) {
            if ([] === array_diff_assoc($axes, array_intersect_key($usages, $axes))) {
                return (string) $name;
            }
        }

        return null;
    }
}
