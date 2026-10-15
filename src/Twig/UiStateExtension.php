<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyPreference\Service\PreferenceService;

/**
 * What the interface remembers, read while the page is drawn: the design
 * system's own preferences, all of them under `ui.`.
 */
class UiStateExtension extends AbstractExtension
{
    private const KEY_PREFIX = 'ui.';

    public function __construct(
        private readonly PreferenceService $preferences,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ui_state_get', [$this, 'uiStateGet']),
            new TwigFunction('ui_state_all', [$this, 'uiStateAll']),
        ];
    }

    public function uiStateGet(string $key, mixed $default = null): mixed
    {
        return $this->preferences->get($key, $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function uiStateAll(): array
    {
        return $this->preferences->all(self::KEY_PREFIX);
    }
}
