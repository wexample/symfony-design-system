<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class DesignSystemExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'button',
                [
                    $this,
                    'button',
                ],
                [
                    self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            ),
        ];
    }

    public function button(
        Environment $twig,
        string $icon,
        string $label,
        array $options = []
    ): string {
        return $twig->render('@WexampleSymfonyDesignSystemBundle/components/button.html.twig', [
            'icon' => $icon,
            'label' => $label,
            'options' => $options,
        ]);
    }
}
