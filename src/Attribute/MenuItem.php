<?php

namespace Wexample\SymfonyDesignSystem\Attribute;

use Attribute;

/**
 * Puts the page a route serves into a menu group, without the menu knowing it exists.
 *
 * A menu written by hand is a list someone has to edit when a page appears, and
 * a page shipped by a bundle has nobody to edit it: the application owning the
 * template has never heard of the bundle. So the page says where it belongs and
 * the menu asks.
 *
 * What the item looks like is deliberately absent. `menu_item` already reads the
 * label and the icon from the page's own translations — `page_title`, `page_icon`
 * — and decides on its own whether it is the page being read. Repeating any of
 * it here would be a second place to keep in step with the first.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MenuItem extends AbstractRouteGroup
{
}
