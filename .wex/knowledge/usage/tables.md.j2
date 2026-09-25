The data table exists twice: `data_table(columns, rows, options)` drawn by the server, and
the `data-table` vue drawn in the browser. Both take the same column definitions and the
same row shapes, and a feature is added to both or to neither. The server one is for what
is known when the page is written; the vue one for rows that come from an api or change
while the page is open.

## Columns and cells

A column is `{ key, label, cell, align, className, secondary }`. `cell` says how a value is
drawn: `date`, `html`, `status` (a marker: a type name, or `{ type, count, title, label }`),
`path` (a file path: the name whole, the folders before it cut from their start — the
`file-path` component, `file_path()` in twig), or nothing for the value as text. A row
carrying only `{ group: 'Label' }` is the line between two runs of rows.

## The bar above the table

One bar, `table--bar`, holds what acts on the table rather than on a row, in this order:
the filters, the selection count and the bulk actions, and the total at its far end. It is
drawn only when one of them is asked for.

- **Selection** — `selectable` puts a box at the head of each row. `bulk_actions` /
  `bulkActions` are `{ label, icon, href, token, all, class }`: one with an `href` posts
  the ticked keys there (under `selection_name`, `ids[]` by default); the vue one also
  emits `bulk-action`. An action marked `all` works with nothing ticked and takes every
  row; `class` lets the one expected lead, `button--invert`.
- **Total** — `show_count` / `showCount` ends the bar on how many rows there are.
- **Filters** — see below.

## Filters

A filter is `{ key, label, options: [{ value, label, count }], multiple }`. Each one is a
discreet menu in the bar, named after the filter while it narrows nothing and after what it
narrows to once it does — "Owner: Design +1", worded by `frontend.filter.summary`. The bar
is the `filter-bar` component, usable on its own.

A filter holds no rows: it produces a value, `{ key: value | [values] }`. What narrows the
rows depends on where they come from.

- **Server table** — the value lives in the page's query (`?owner[0]=design`). Each option
  is a link to the same page with that option turned, and the page number dropped. The
  controller reads the same query to narrow what it lists; in twig, `filter_selected(key)`
  gives what a filter holds.
- **Vue table fed by an api** — the default. `filters` and `v-model:filter-values`: the page
  asks its api with the value and hands back rows already narrowed.
- **Vue table holding its rows** — `filter-rows` makes the table narrow them itself, a row
  passing when its field of the filter's key is one of the values (or, for a list, holds
  one). The table goes back to its first page, and ticked rows a filter hides are unticked.

## Rows shown

`striped` and `hover` for tables read across. `page_size` does not exist on the server,
where the controller pages; `pageSize` on the vue one pages the rows it holds, with the
design system's pagination under the table.
