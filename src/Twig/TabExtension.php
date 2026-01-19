<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;

class TabExtension extends AbstractTemplateExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'tab_item',
                function (Environment $twig, string $label, string $href, bool $active = false, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/tab-item.html.twig',
                        [
                            'label' => $label,
                            'href' => $href,
                            'active' => $active,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
