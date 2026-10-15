<?php

namespace Wexample\SymfonyDesignSystem\Enum;

/**
 * Where what the interface remembers is kept, as the app chooses it through
 * the `wexample_symfony_design_system.ui_state.storage` parameter.
 */
enum UiStateStorage: string
{
    // The visitor's session: nothing to set up, gone with the session.
    case Session = 'session';

    // The database, one memory per signed-in user, following them from one
    // machine to another; a visitor not signed in keeps theirs in the session.
    case User = 'user';

    // The database, one memory for everybody: a tool with no accounts, used by
    // the one person running it.
    case Shared = 'shared';

    public function isDatabase(): bool
    {
        return self::Session !== $this;
    }
}
