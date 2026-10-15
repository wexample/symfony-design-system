<?php

namespace Wexample\SymfonyDesignSystem\Service\UiState;

use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyDesignSystem\Interface\UiStateStorageInterface;

/**
 * The session, under one namespace, so what belongs to the interface is told
 * apart from the rest of the session at a glance. It holds for someone who
 * never signed in and costs no schema; it is gone with the session.
 */
class SessionUiStateStorage implements UiStateStorageInterface
{
    private const NAMESPACE = 'ui_state.';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->requestStack->getSession()->get(self::NAMESPACE.$key, $default);
    }

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

    public function set(string $key, mixed $value): void
    {
        $session = $this->requestStack->getSession();

        if (null === $value) {
            $session->remove(self::NAMESPACE.$key);

            return;
        }

        $session->set(self::NAMESPACE.$key, $value);
    }
}
