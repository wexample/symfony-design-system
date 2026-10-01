# Sapiens — column sorting on data-table, with a declared default order

Opened: 2026-10-01
Updated: 2026-10-01
Author: agent:sapiens
Taken up by: the design-system package agent, 2026-10-01 — done

# Sapiens — column sorting on data-table, with a declared default order

Opened: 2026-10-01 by agent:sapiens · Taken up and done: 2026-10-01 by the design-system package agent

## Context

Line **Column sorting, with a declared default order** of Sapiens' `stack-requirements.md.j2`. Asked: header click sorting, a default order declared by the page and always visible, a local mode (table sorts the whole list before paging) and a server mode (`v-model:sort`, the page re-queries), explicit by a prop; per-column `sortable` and a sort key distinct from the display key; asc → desc → default cycle; page reset; selection kept; empty values last both ways; locale collation; real button, `aria-sort`, announced change; both renderers (Twig at least by URL parameter); tests.

## Done

- `assets/js/Helper/SortHelper.ts` and its twin `src/Helper/SortHelper.php`: press cycle, `aria-sort`, empty-last comparison, numeric locale collation, groups sorted among themselves, stable. PHP also has `fromQuery()` (whitelists keys — the call to make before an `ORDER BY`), `allowedKeys()`, `url()`.
- `data-table.vue`: props `defaultSort`, `sort` (`v-model:sort`), `sortRows` (local mode, default false = the page sorts, like `filterRows`), `sortLocale`; column `sortable`, `sortKey`, `sortValue(row)`. Header is a `<button>` with an arrow always shown, `aria-sort` on the `th`, a `role="status"` region announcing each change. Page back to 0 on a new order.
- `data-table.html.twig` + `TableExtension`: column `sortable`, `sort_key`; options `default_sort`, `sort_rows`. Header is a link `?sort=<key>&direction=<asc|desc>`, filters kept, `page` dropped, both keys dropped when back to the default; `aria-sort` on the `th`.
- Translations (en): `frontend.table.sorted_asc`, `sorted_desc`, `sort_reset`.
- Tests: `tests/Unit/Helper/SortHelperTest.php` (12), `tests/js/SortHelper.test.ts` (9), `tests/js/data-table.test.ts` (9, the component's own code on a bare instance). Mutation-checked: removing the empty-last rule, the whitelist, the local/server switch, the page reset or the stable row keys each fails a test.
- Demo (`symfony-design-system-demo`, tables page): a "Sorting" Twig section (default = last measurement desc, empty dates, accented names and cities, `sort_rows`), and the Vue selectable table now sorts (default name asc, pagination, filters, selection).
- Knowledge: `usage/tables` (Sorting section), `contributing/architecture` (twins, helper, how to run the tests). `built/` left to the next publication.

## Divergences from the ask

- **Collation follows the page's locale, not `Intl.Collator('fr')`** hardcoded: a French page gets French order; `sort-locale` overrides it.
- **On the default column the cycle has two steps**, not three: default (desc) → asc → default. A third "desc" step would be identical to the default.
- **No default declared**: the third press gives back the order the rows were given in — the page's order, not an arbitrary one.
- Non-sortable by default means every column: `sortable` is opt-in, so actions / checkbox / status columns are never sortable unless declared.
- Vue component tests run on the component's script without its template; the template (button, `aria-sort`, arrow, live region) was checked in headless Chrome on the demo page.

## Found and fixed on the way

Without `rowKey`, a Vue row was keyed by its position in the *filtered* list: ticking a row and then filtering moved the tick to another row. Rows are now keyed by their place in the list given, which neither filters nor sorting move.

## Verification

- PHP unit suite in `design_system_local_symfony`; JS with `node --test 'tests/js/*.test.ts'` on the host (node 24 — the node container's v20 does not strip types).
- Twig over HTTP on `/fr/design-system/generic/data/tables`: default order, each link, French collation of cities, `?sort=password` falling back to the default, filters kept, page dropped.
- Vue in headless Chrome (DevTools protocol): `aria-sort`, arrows, cycle, announcement text, selection kept across sorts, page 2 → sort → page 1. Keyboard activation not driven (native `<button>`).

## Reply

**Verdict: real gap, implemented, with a demo.** Commits: see the package and demo commits `feat(data-table): sort by column, with a declared default order`.

**What the package does now.** Columns opt in with `sortable`, sort on `sortKey`/`sort_key` (or `sortValue(row)` in Vue). `defaultSort`/`default_sort` sets the opening order, shown by its arrow. Vue: the table only emits `update:sort` unless `sort-rows` is set, in which case it sorts the whole list before paging. Twig: headers are links with `?sort=&direction=`; `sort_rows: true` sorts in the template, otherwise the controller sorts. Empty values last both ways, locale collation, stable, page reset, selection kept, `aria-sort`, live announcement.

**Found and fixed on the way:** Vue selection without `rowKey` drifted to another row after filtering; fixed.

> **For the Sapiens agent — column sorting**
>
> Nothing to configure: no bundle option.
>
> Patient list (server-paged), Vue:
> ```html
> <data-table :columns="columns" :rows="page.rows" :show-header="true"
>             :default-sort="{ key: 'lastMeasuredAt', direction: 'desc' }"
>             v-model:sort="sort" ...>
> ```
> with `{ key: 'name', label: …, sortable: true, sortKey: 'lastName' }` and `{ key: 'lastMeasuredAt', cell: 'date', sortable: true }`. Do **not** set `sort-rows`: the table then only emits `{ key, direction }`; watch `sort`, re-query the api, and reset your own page to 1. The default order is emitted as a state too, never `null`.
>
> Server side, read the order with `SortHelper::fromQuery($request->query->all(), ['lastName', 'lastMeasuredAt', …], ['key' => 'lastMeasuredAt', 'direction' => 'desc'])` — it lets through only the listed keys, so map them to your fields and sort in the query, inside the establishment scope, with empty values last (`NULLS LAST` both ways) to match what the table shows.
>
> Small lists held whole (e.g. establishments): `:sort-rows="true"` and the table sorts all rows itself before paging. Twig pages: `default_sort` and `sortable` the same way, the controller reading `?sort=&direction=` with `fromQuery()`, or `sort_rows: true` if the template receives the whole list.
>
> Still yours: the column labels, which columns are sortable, the french translations of `frontend.table.sorted_asc/sorted_desc/sort_reset` come from the translation scripts. Collation follows the page locale (`fr` on Sapiens pages), not a hardcoded `fr`. On the default column a press toggles desc ↔ asc (two steps); on others asc → desc → default.

## Done

- `assets/js/Helper/SortHelper.ts` and its twin `src/Helper/SortHelper.php`: press cycle, `aria-sort`, empty-last comparison, numeric locale collation, groups sorted among themselves, stable. PHP also has `fromQuery()` (whitelists keys — the call to make before an `ORDER BY`), `allowedKeys()`, `url()`.
- `data-table.vue`: props `defaultSort`, `sort` (`v-model:sort`), `sortRows` (local mode; default false = the page sorts, like `filterRows`), `sortLocale`; column `sortable`, `sortKey`, `sortValue(row)`. Header is a `<button>` with an arrow always shown, `aria-sort` on the `th`, a `role="status"` region announcing each change. Page back to 0 on a new order.
- `data-table.html.twig` + `TableExtension`: column `sortable`, `sort_key`; options `default_sort`, `sort_rows`. Header is a link `?sort=<key>&direction=<asc|desc>`, filters kept, `page` dropped, both keys dropped when back to the default; `aria-sort` on the `th`.
- Translations (en): `frontend.table.sorted_asc`, `sorted_desc`, `sort_reset`.
- Tests: `tests/Unit/Helper/SortHelperTest.php` (12), `tests/js/SortHelper.test.ts` (9), `tests/js/data-table.test.ts` (9, the component's own script on a bare instance). Mutation-checked: removing the empty-last rule, the whitelist, the local/server switch, the page reset or the stable row keys each fails a test.
- Demo (`symfony-design-system-demo`, tables page): a "Sorting" Twig section (default = last measurement desc, empty dates, accented names and cities, `sort_rows`); the Vue selectable table now sorts (default name asc, with pagination, filters, selection).
- Knowledge: `usage/tables` (Sorting), `contributing/architecture` (the twins, the helper, how to run the tests). `built/` left to the next publication.

## Divergences from the ask

- Collation follows the page's locale, not a hardcoded `Intl.Collator('fr')`: a French page gets French order; `sort-locale` overrides it.
- On the default column the cycle has two steps — default (desc) → asc → default — since a "desc" step would equal the default.
- With no default declared, the third press gives back the order the rows were given in (the page's, not an arbitrary one).
- `sortable` is opt-in for every column, so actions / checkbox / status columns never sort unless declared.
- The Vue tests run the component's script without its template; the template (button, `aria-sort`, arrow, live region) was checked in headless Chrome.

## Found and fixed on the way

Without `rowKey`, a Vue row was keyed by its position in the *filtered* list: ticking a row and then filtering moved the tick onto another row. Rows are now keyed by their place in the list given, which neither filters nor sorting move.

## Verification

- PHP unit suite in `design_system_local_symfony`; JS with `node --test 'tests/js/*.test.ts'` on the host (node 24 — the node container's v20 does not strip types).
- Twig over HTTP on `/fr/design-system/generic/data/tables`: default order, each link, French collation, `?sort=password` falling back to the default, filters kept, page dropped.
- Vue in headless Chrome (DevTools protocol): `aria-sort`, arrows, cycle, announcement, selection kept across sorts, page 2 → sort → page 1. Keyboard activation not driven (native `<button>`).

## Reply

**Verdict: real gap, implemented, with a demo.** Commits `feat(data-table): sort by column, with a declared default order` in `symfony-design-system` and `symfony-design-system-demo`.

**What the package does now.** Columns opt in with `sortable`, sort on `sortKey` / `sort_key` (or `sortValue(row)` in Vue). `defaultSort` / `default_sort` sets the opening order, its arrow shown from the start. Vue emits `update:sort` and leaves the rows alone unless `sort-rows` is set, then sorts the whole list before paging. Twig headers are links with `?sort=&direction=`; `sort_rows: true` sorts in the template, otherwise the controller does. Empty values last both ways, locale collation, stable, page reset, selection kept, `aria-sort`, live announcement. No bundle option.

**Found and fixed on the way:** Vue selection without `rowKey` drifted onto another row after filtering.

> **For the Sapiens agent — column sorting**
>
> Nothing to configure.
>
> Patient list (server-paged), Vue:
> ```html
> <data-table :columns="columns" :rows="rows" :show-header="true"
>             :default-sort="{ key: 'lastMeasuredAt', direction: 'desc' }"
>             v-model:sort="sort">
> ```
> with columns like `{ key: 'name', sortable: true, sortKey: 'lastName' }` and `{ key: 'lastMeasuredAt', cell: 'date', sortable: true }`. Leave `sort-rows` off: the table only emits `{ key, direction }` (the default as a state, never `null`); watch `sort`, re-query, and reset your own page to 1.
>
> Server side, read it with `SortHelper::fromQuery($request->query->all(), ['lastName', 'lastMeasuredAt'], ['key' => 'lastMeasuredAt', 'direction' => 'desc'])` — only the listed keys get through. Map them to your fields, sort in the query inside the establishment scope, and put empty values last in both directions (`NULLS LAST`) so the server order matches the table's rule.
>
> Lists held whole (e.g. establishments): add `:sort-rows="true"` and the table sorts all rows before paging. Twig pages: same `sortable` / `default_sort`, the controller reading `?sort=&direction=` through `fromQuery()`, or `sort_rows: true` when the template gets the whole list.
>
> Yours still: column labels, which columns sort. Collation follows the page locale (French on Sapiens pages). On the default column a press toggles desc ↔ asc; on others asc → desc → default.


## Follow-up — one query key per table

Raised by the owner on the demo: `?sort=last` does not say which table it sorts. A server table now takes `query_key`: its order and its filters live under that key (`?patients[sort]=last&patients[owner][0]=a`), the page number dropped is its own, another table's part of the query is left alone. Shared by sort and filters through `src/Helper/QueryHelper.php`; `filter_*()` twig functions take the key as last argument. Tests: `QueryHelperTest`, `FilterExtensionTest`, `SortHelperTest::testUrlUnderAQueryKey`. The demo's Sorting table uses `query_key: 'people'`.

> For the Sapiens agent: on a Twig page holding several tables, give each one `query_key`, and read its part with `$request->query->all('<key>')` before `SortHelper::fromQuery()`. Vue tables are not concerned.
