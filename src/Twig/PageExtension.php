<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;

class PageExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'page_init',
                [
                    $this,
                    'pageInit',
                ]
            ),
            new TwigFunction(
                'page_name_from_route',
                [
                    $this,
                    'pageNameFromRoute',
                ]
            ),
        ];
    }

    public function pageInit(
        string $pageName
    ) {

    }

    public function pageNameFromRoute(string $route): string
    {
        return '';
    }
}
