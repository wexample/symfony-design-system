<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class FormExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'form_submit',
                function (Environment $twig, $context, string $icon, string $label, array $options = []) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/button',
                        [
                            'icon' => $icon,
                            'label' => $label,
                            'options' => array_merge(['type' => 'submit'], $options),
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
