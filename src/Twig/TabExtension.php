<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\NavigationService;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class TabExtension extends AbstractTemplateExtension
{
    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly NavigationService $navigation,
    ) {
        parent::__construct($componentsExtension);
    }

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
            // Given a navigation (NavigationService) or its bare items, of
            // which the tabs show the level the page stands in; the options
            // say better what the navigation says. No way back unless the
            // navigation or the options give one: the pages of a section are
            // reached from the menu, which already tells where one is.
            new TwigFunction(
                'page_tabs',
                function (Environment $twig, $context, array $navigation, array $options = []) {
                    $navigation = NavigationService::currentLevel($this->navigation->normalize($navigation));

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/page-tabs',
                        [
                            'items' => $navigation['items'],
                            'back' => $options['back'] ?? $navigation['back'] ?? false,
                            'actions' => $options['actions'] ?? $navigation['actions'],
                            'label' => $options['label'] ?? $navigation['label'],
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
