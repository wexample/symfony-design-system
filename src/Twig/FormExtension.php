<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class FormExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            // A form's actions — its buttons, and links beside them — in one row:
            // in its own template by default, or wherever whoever placed it
            // has room (`outside: true`) when it was loaded with `actions:
            // false`, its buttons sending it from there.
            new TwigFunction(
                'form_actions',
                function (Environment $twig, $context, $form, array $options = []) {
                    return $twig->render('@WexampleSymfonyDesignSystemBundle/partials/form_actions.html.twig', [
                        'render_pass' => $context['render_pass'] ?? null,
                        'form' => $form,
                        'outside' => $options['outside'] ?? false,
                        'secondary' => $options['secondary'] ?? [],
                    ]);
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
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
