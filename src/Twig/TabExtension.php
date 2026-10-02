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
                    $context,
                    string $label,
                    string $route,
                    array $routeParams = [],
                    array $options = [],
                    ?string $icon = null
                ) {
                    if (null !== $icon) {
                        $options['icon'] = $icon;
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/tab-item',
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
                function (Environment $twig, $context, string $label, string $href, array $options = [], ?string $icon = null) {
                    if (null !== $icon) {
                        $options['icon'] = $icon;
                    }

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/tab-item',
                        [
                            'label' => $label,
                            'href' => $href,
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            // The views of one thing — a record and its measurements — as a
            // bar under the header: the way back to the list it was opened
            // from, its tabs, and what can be done to it at the far end.
            new TwigFunction(
                'page_tabs',
                function (Environment $twig, $context, array $items, array $options = []) {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/page-tabs',
                        [
                            'items' => $items,
                            'back' => $options['back'] ?? true,
                            'actions' => $options['actions'] ?? null,
                            'label' => $options['label'] ?? null,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
