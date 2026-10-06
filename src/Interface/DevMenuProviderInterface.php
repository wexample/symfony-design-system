<?php

namespace Wexample\SymfonyDesignSystem\Interface;

/**
 * What a bundle puts in the development menu, opened from the « dev » marker
 * of every page's footer outside production: the mailbox of symfony-mail-ds,
 * switching account in symfony-user-ds, an app's API description. Each says
 * itself whether it exists — a route routed in dev only, a firewall allowing
 * it —, so the menu holds only what can be reached.
 */
interface DevMenuProviderInterface
{
    public const string TAG = 'wexample_symfony_design_system.dev_menu_provider';

    /**
     * @return list<array<string, mixed>> button_menu items: `icon`, `label` (a
     *                                    translation key or words), `href`,
     *                                    and `new_window` or `target`;
     *                                    `account: true` for an action on the
     *                                    account signed in, listed after the
     *                                    links, `disabled` while nobody is;
     *                                    `order` to place it in its part
     *                                    (0 by default, lower first)
     */
    public function getDevMenuItems(): array;
}
