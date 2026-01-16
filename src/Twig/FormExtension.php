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
                function (Environment $twig, string $icon, string $label, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/button.html.twig',
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
