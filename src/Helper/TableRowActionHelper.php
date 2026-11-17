<?php

namespace Wexample\SymfonyDesignSystem\Helper;

/**
 * The actions a server hands to a row of a table — the `actions` cell, twig
 * or vue: `{ href, icon, label, method, token, target, target_options,
 * confirm, rel }`. Opening, editing and deleting are written here once, so
 * that every table draws them with the same icon, the same name and in the
 * same place; what the row offers besides is a `link()` or a `post()`.
 *
 * `rel` says what an action is to the row. The table sets the actions that
 * carry one in this order — open, edit, the others, delete — and a column
 * given `link: 'open'` leads where the open action leads.
 */
class TableRowActionHelper
{
    public const string REL_OPEN = 'open';
    public const string REL_EDIT = 'edit';
    public const string REL_DELETE = 'delete';

    /**
     * Where the row is read or worked on at length: its page, its editor —
     * or a panel showing it, where it has no page of its own.
     */
    public static function open(
        string $href,
        ?string $target = null,
        string $label = 'Open',
    ): array {
        return self::link($href, 'ph:bold/eye', $label, $target, self::REL_OPEN);
    }

    /**
     * A quick edit, in a panel beside the list.
     */
    public static function edit(
        string $href,
        string $label = 'Edit',
    ): array {
        return self::link($href, 'ph:bold/pencil-simple', $label, 'panel', self::REL_EDIT);
    }

    /**
     * Posted, and asked first: `$message` says what is lost.
     */
    public static function delete(
        string $href,
        string $token,
        string $title,
        string $message,
        string $label = 'Delete',
    ): array {
        return self::post($href, $token, 'ph:bold/trash', $label, [
            'title' => $title,
            'message' => $message,
            'accept' => $label,
        ], self::REL_DELETE);
    }

    /**
     * A deletion that asks more than a confirmation — what replaces the row
     * where it is used —, the question in a panel.
     */
    public static function deleteIn(
        string $href,
        string $target = 'panel',
        string $label = 'Delete',
    ): array {
        return self::link($href, 'ph:bold/trash', $label, $target, self::REL_DELETE);
    }

    public static function link(
        string $href,
        string $icon,
        string $label,
        ?string $target = null,
        ?string $rel = null,
    ): array {
        return array_filter([
            'href' => $href,
            'icon' => $icon,
            'label' => $label,
            'target' => $target,
            'rel' => $rel,
        ], static fn ($value): bool => null !== $value);
    }

    /**
     * What changes the row and stays on the list: a form posted with its
     * token, asked first when `$confirm` ({ title, message, accept }) says so.
     */
    public static function post(
        string $href,
        string $token,
        string $icon,
        string $label,
        ?array $confirm = null,
        ?string $rel = null,
    ): array {
        return array_filter([
            'href' => $href,
            'method' => 'post',
            'token' => $token,
            'icon' => $icon,
            'label' => $label,
            'confirm' => $confirm,
            'rel' => $rel,
        ], static fn ($value): bool => null !== $value);
    }
}
