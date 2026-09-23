<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * What the interface remembers of a visitor between two pages.
 *
 * The session and not the database: it holds for someone who never signed in,
 * it is already per user once they have, and it costs no schema. An app that
 * wants these choices to follow a user from one machine to another overrides
 * `App::persistUiState` on the client and never reaches this controller.
 *
 * Keys read like paths — `ui.layout.menu.left`, `ui.layout.zone.<id>.size` —
 * and live under one session namespace, so what belongs to the interface is
 * told apart from the rest of the session at a glance.
 */
class UiStateService
{
    private const NAMESPACE = 'ui_state.';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function get(
        string $key,
        mixed $default = null,
    ): mixed {
        return $this->requestStack->getSession()->get(self::NAMESPACE.$key, $default);
    }

    /**
     * Everything the interface remembers, keyed without the namespace — what the
     * layout hands to the page once, so a component restoring its state reads it
     * where it stands instead of every page passing it down.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $state = [];

        foreach ($this->requestStack->getSession()->all() as $key => $value) {
            if (str_starts_with($key, self::NAMESPACE)) {
                $state[substr($key, strlen(self::NAMESPACE))] = $value;
            }
        }

        return $state;
    }

    public function set(
        string $key,
        mixed $value,
    ): void {
        $this->requestStack->getSession()->set(self::NAMESPACE.$key, $value);
    }
}
