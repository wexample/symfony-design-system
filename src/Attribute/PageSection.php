<?php

namespace Wexample\SymfonyDesignSystem\Attribute;

use Attribute;

/**
 * Puts what a route renders into a named zone of somebody else's page.
 *
 * The counterpart of `MenuItem` for the body rather than the column: a page
 * declares the zones it offers, `page_sections` fills them, and a bundle adds
 * to a page the application never knew it would share.
 *
 * A section is a route and not a template on purpose. A zone worth having needs
 * its own data — a repository, a service, a query the page's owner cannot
 * guess — and a template would have to be handed a context nobody can write.
 * A controller carries its own, and is rendered as a sub-request where the zone
 * is drawn.
 *
 * So the route serves a fragment and not a page: no layout, no menu, just the
 * markup that goes in the hole. Its path says so by opening on an underscore,
 * since nobody opens it by hand.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PageSection extends AbstractRouteGroup
{
}
