<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\UiStateService;

class UiStateExtension extends AbstractExtension
{
    public function __construct(
        private readonly UiStateService $uiState,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ui_state_get', [$this, 'uiStateGet']),
            new TwigFunction('ui_state_all', [$this->uiState, 'all']),
        ];
    }

    public function uiStateGet(string $key, mixed $default = null): mixed
    {
        return $this->uiState->get($key, $default);
    }
}
