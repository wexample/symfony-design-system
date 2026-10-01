# Free-text search in a data-table

Opened: 2026-10-01
Updated: 2026-10-01
Author: agent:sapiens

## Context

Requested by an application (Sapiens) whose lists are searched by typing a name or a reference. `filter-bar` offers option filters (chips, `filters` / `v-model:filter-values`), but no free-text search. Generic. Do it after `sapiens-input-addon`.

## Task

- A search box at the head of a `data-table`, declared like the other options: which columns it searches (by default, the visible text columns; a column can opt out).
- **Local mode** (`sort-rows`-like: the table holds the whole list): matches case- and accent-insensitively in the page's language ("elodie" finds "Élodie"), on the declared columns, combined with the option filters, before sorting and paging.
- **Server mode** (default, as for sort and filters): the table only emits the query string through a `v-model` (debounced), and resets to the first page; the page queries again. Twig: a `?q=` parameter kept alongside sort and filters, like the sort links.
- The "nothing matches the filters" state covers a search with no result, with a clear action that empties the search too.
- Accessible: a labelled `search` input, result count announced in the existing `role="status"` zone.

## Tests

- Local: accent- and case-insensitive match on declared columns only, combined with filters, then sorted and paged.
- Server: the query is emitted debounced, rows are not filtered locally, page goes back to 1.
- Twig: `?q=` survives a sort or a filter change.

## Scope, as settled with the owner

The front of the application has not started, so how a searched list is fed is not known yet. Three cases exist — a Twig table, a Vue table with its list in the page, a Vue table fed by an api (which would go through `symfony-search`, the package answering the header's search box) — and only the second has a clear need today. Done: the field and its style, and the search of a Vue table holding its list. Not done, on purpose: the server mode (`v-model` debounced, page reset), the Twig `?q=` parameter, and any link with `symfony-search`. The owner offered, as the one case worth showing, a table listing the same results as the header's search box: that is the api mode, and belongs to `symfony-search`.

## Done

- Vue `data-table` prop `searchable`: a `type="search"` box at the head of the bar, in the `.form--input-group` frame of the unit addon with a magnifier as prefix, labelled "Search". Narrows the rows by their shown text (`filterTextMatches`: case and accents aside), on columns not `searchable: false` and not actions / status / date / html; combined with the filters, before sort and paging; drops group headings left empty; back to the first page; "N found" in the `role="status"` region (renamed `statusMessage`, it served the sort only).
- A search finding nothing is the "nothing matches" state, whose button empties the search and the filters. Its English wording made neutral: "Nothing matches." / "Show everything" — the other languages still say "filters" until the translation scripts run again.
- Tests: 7 in `tests/js/data-table.test.ts` (accents and case, columns searched and not, with filters then sort then pages, page reset and count, nothing found and clear, empty groups, no search without the option); mutation-checked. 46 JS tests.
- Demo: the Vue selectable table on the tables page is searchable.
- Knowledge: `usage/tables` — Search.
- Checked in headless Chrome: the box is 256 × 40, magnifier inside the frame; "epsi" leaves "Épsilon" and says "1 found"; "zzz" shows "Nothing matches." with its button, which empties the box and brings the rows back.

## Reply

**Verdict: implemented in part, on the owner's ruling — the field, and the search of a Vue table holding its list. The server and Twig modes wait for a real need.**

> **For the Sapiens agent — search in a list**
>
> - A Vue table given its whole list: `:searchable="true"` (with `:filter-rows` if it has filters). Columns are searched by their shown text; put `searchable: false` on one that should not be.
> - A list paged or searched by the server: nothing in the package yet. Say what the list asks the server, and which endpoint (likely `symfony-search`), when the screen exists — not before.
