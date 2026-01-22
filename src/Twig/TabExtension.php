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
                function (
                    Environment $twig,
                    string $label,
                    string $route,
                    array $routeParams = [],
                    array $options = []
                ) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/tab-item.html.twig',
                        [
                            'label' => $label,
                            'route' => $route,
                            'route_params' => $routeParams,
                            'href' => $twig->getFunction('path')->getCallable()($route, $routeParams),
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'tab_item_link',
                function (Environment $twig, string $label, string $href, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/tab-item.html.twig',
                        [
                            'label' => $label,
                            'href' => $href,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
