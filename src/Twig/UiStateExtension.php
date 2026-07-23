<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UiStateExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ui_state_get', [$this, 'uiStateGet']),
        ];
    }

    public function uiStateGet(string $key, mixed $default = null): mixed
    {
        return $this->requestStack->getSession()->get('ui_state.' . $key, $default);
    }
}
