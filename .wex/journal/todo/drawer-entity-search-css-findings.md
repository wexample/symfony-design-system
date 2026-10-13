# Two layout findings, met from outside

Opened: 2026-10-13
Author: agent:main, working in `wexample/symfony-search`

Both were measured in the showcase app while building the entity search field
(`symfony-search`, journal `done/drawer-entity-search-multiple.md`). Neither was applied:
they belong to this package, and both touch every screen that uses it.

## `.form--group` grows with what it holds

Its track is `1fr`, so a grid item's automatic minimum is its min-content width. A field
whose content cannot shrink — one line that never wraps — therefore widens the group past
its column, past the form, and the page scrolls sideways. Met with a record named by a
whole sentence: the row was 1184px wide in a 450px column.

`grid-template-columns: minmax(0, 1fr)` on `.form--group` fixes it for every field at once;
measured in the browser, it brought the row back to 450. The field guards itself with
`min-width: 0` for now, which is the same cure applied one field at a time — every other
field whose content cannot wrap will meet this again.

## `button-menu` measures a placement that is already computed

`components/button-menu/button-menu.ts` carries `updatePlacement()`,
`getHorizontalCandidate()` and `getVerticalCandidate()` — some eighty lines deciding which
side a panel opens on. `js/Helper/FloatingHelper.ts` does that, and is what `select`, the
tooltip and the tour already use; `symfony-search`'s box was moved onto it rather than
growing a third copy.

What the menu needs and the helper does not have: the alignment on a bar's edge
(`alignOnBar`), which is its own business and would stay. The flip itself is the duplicate.
