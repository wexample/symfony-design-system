<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

class AppExtension extends AbstractTemplateExtension
{
    public function __construct(
        ComponentsExtension $componentsExtension,
        private readonly RouterInterface $router,
        private readonly ?string $appHomeRoute = null,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_home_url', [$this, 'appHomeUrl']),
        ];
    }

    public function appHomeUrl(): string
    {
        $route = $this->appHomeRoute;

        if (! $route) {
            return '#';
        }

        try {
            return $this->router->generate($route);
        } catch (RouteNotFoundException) {
            return '#';
        }
    }
}
