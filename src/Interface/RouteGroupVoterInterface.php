<?php

namespace Wexample\SymfonyDesignSystem\Interface;

/**
 * Says that a route, though it declared a group, does not belong in it here.
 *
 * The registry knows what every route asked for, and nothing about the
 * application asking. A board serving many apps needs a page contributed by one
 * app's bundle to appear on that app and not on its neighbours — a rule the
 * design system has no way to state, and the application no way to enforce
 * without rewriting the collection.
 *
 * So the rule is plugged in: implement this, and it is asked about every route
 * of every group, on every request. No voter is the ordinary case — a run then
 * holds exactly what declared itself into it.
 */
interface RouteGroupVoterInterface
{
    public const string TAG = 'wexample.symfony_design_system.route_group_voter';

    /**
     * True to leave this route out of the run it declared itself in.
     */
    public function excludes(string $route): bool;
}
