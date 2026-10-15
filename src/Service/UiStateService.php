<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Wexample\SymfonyDesignSystem\Interface\UiStateStorageInterface;

/**
 * What the interface remembers of a visitor between two pages: a menu folded,
 * a zone dragged wider, a tool left open.
 *
 * Kept where the app chose (`wexample_symfony_design_system.ui_state.storage`,
 * see UiStateStorage): the session by default, or the database — per user, or
 * shared by everybody in a tool without accounts. Written by the client through
 * UiStateController and read back here while the page is drawn, so it arrives
 * as it was left rather than jumping to it; both ends go through this service,
 * which is why changing the storage needs nothing on the client.
 *
 * Keys read like paths — `ui.layout.menu.left`, `ui.layout.zone.<id>.size`.
 */
class UiStateService
{
    public function __construct(
        private readonly UiStateStorageInterface $storage,
    ) {
    }

    public function get(
        string $key,
        mixed $default = null,
    ): mixed {
        return $this->storage->get($key, $default);
    }

    /**
     * Everything the interface remembers — what the layout hands to the page
     * once, so a component restoring its state reads it where it stands instead
     * of every page passing it down.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->storage->all();
    }

    public function set(
        string $key,
        mixed $value,
    ): void {
        $this->storage->set($key, $value);
    }
}
