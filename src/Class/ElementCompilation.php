<?php

namespace Wexample\SymfonyDesignSystem\Class;

/**
 * What putting a bundle's declarations beside its scan gave: the registry that
 * follows when they agree, and every point on which they do not.
 *
 * Both are always returned. A problem does not empty the inventory, so a page
 * can show what it has and name what is wrong beside it; and a check does not
 * need the inventory to say the build must fail.
 */
class ElementCompilation
{
    /**
     * @param string[] $problems  one sentence each, naming the element: a
     *                            contradiction, and the registry is not written
     * @param string[] $pending   a format an element is not found in and its
     *                            declaration says nothing about — a decision
     *                            not yet made. Counted and named, never fatal:
     *                            the day every element has decided every format
     *                            is the day this list is empty, and a build that
     *                            failed until then would never have run
     * @param string[] $todo      a format an element's declaration wants and
     *                            does not have. Named, not fatal: an
     *                            acknowledged gap is work someone can pick up,
     *                            where an undecided one is nobody's
     */
    public function __construct(
        public readonly ElementInventory $inventory,
        public readonly array $problems = [],
        public readonly array $pending = [],
        public readonly array $todo = [],
    ) {
    }

    public function isClean(): bool
    {
        return $this->problems === [];
    }
}
