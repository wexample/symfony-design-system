<?php

namespace Wexample\SymfonyDesignSystem\Attribute;

/**
 * What every attribute putting a route into a named run has in common.
 *
 * A page declares where it belongs and whoever draws that place asks for it.
 * The shape is always the same — a group to join, a rank inside it — so the
 * registry collecting them needs to know nothing about what the group is for:
 * a column of menu items and a stack of sections on a page are the same
 * question asked twice.
 */
abstract class AbstractRouteGroup
{
    /**
     * @param string $group  the run this route joins, named by whatever draws it
     * @param int    $weight rank inside the group, lightest first. Declaration order would
     *                       be installation order, which is to say no order at all
     */
    public function __construct(
        public readonly string $group,
        public readonly int $weight = 0,
    ) {
    }
}
