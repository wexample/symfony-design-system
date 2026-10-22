# Editable grid (spreadsheet-like data-table mode)

Opened: 2026-10-09
Updated: 2026-10-13
Author: agent:archeology-drawers

## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It comes from the 2026-10 "bottom of the drawers" archaeology pass. Read it, then discuss it with the owner: every design choice below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - **Before anything else**: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/.wex/local/package-agent-intro.md` — where you stand in the suite, what a package must look like, how to work, and how to judge a drawer todo.
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2`, `sources.md.j2`, and the drawers page linked below.
> - Safety: `NETWORK/local/network` runs on production data — read its code only, never run anything against it. Anonymize anything taken from it. Never copy secrets found in its history.

Drawers page: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/drawers-bundles-components.md.j2`, section "Spreadsheet-like editable table (`table_editable`)".

Related, already written:
- `done/extract-from-network.md` item 7 in this journal (a single inline-editable field and the editable list), closed there and folded into this todo: decide both here;
- `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/accounting-invoices.md.j2` (the prod invoice-item editor, the Vue 2 descendant of this component);
- `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/packages/wexample/symfony-loader/.wex/journal/todo/drawer-save-queue-indicator.md` (the "sending… / saved" status this grid used).

## What it is

network had, from 2018-10 to 2020-02, a generic HTML table where every cell is edited in place and saved one cell at a time. It worked like a small spreadsheet:
- **Escape** restores the old value.
- **Enter** goes down to the same column on the next row.
- **Tab** and **Shift+Tab** go to the next or previous editable cell, wrapping across rows.
- An empty **NEW** row always sits at the bottom. Typing in it creates the entity: the row gets a temporary id until the server answers, and a fresh NEW row appears at once.
- A **multi-line paste** from a spreadsheet (rows on `\n`, columns on `\t`) fills the cells from the cursor onward. Rows that do not exist yet are created, all in one batch request.
- **Computed columns** (total = quantity × price, or duration in days × price) re-render on every change in their row.
- **Typed cells**: string, number (comma becomes dot, invalid becomes 0), price (with currency), quantity, duration ("1h30m" or "2j" converted to days at 7 h per day), button, delete button. The delete button is disabled on rows that have no id yet.
- A hovered cell shows a thin rounded border; the active cell shows an input with an inset shadow and its text selected. A status line above the table says "sending…" in orange, then "saved" in green, fading out over 4 s.

It served invoice items (in prod for about a year), a rate card and a demo. The prod editor that replaced it saves the whole list in one POST and lost the Tab navigation and the per-cell autosave.

`symfony-design-system` today has `data-table` (read-only, with filter, sort, pagination and row links) and nothing editable.

## Sources

Archive repo `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/repo.git`; best version is commit `f6932874e` (2019-05-11).

```bash
cd /home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/repo.git
B=project/src/Wex/BaseBundle
git show f6932874e:$B/Resources/js/components/table_editable/TableEditable.js
git show f6932874e:$B/Resources/js/components/table_editable/TableEditableLine.js
git show f6932874e:$B/Resources/js/components/table_editable/Cell/EditableString.js   # keyboard, paste, autosave
git show f6932874e:$B/Resources/js/components/table_editable/Cell/{Default,EditableNumber,EditablePrice,EditableDuration,Eval,Button,ButtonDelete}.js
git show f6932874e:$B/Resources/js/class/AjaxQueue.js
git show f6932874e:$B/Resources/js/class/AjaxQueueItem.js
git show f6932874e:$B/Resources/css/components/_table_editable.scss
git show f6932874e:$B/Resources/templates/macros/components/table_editable.html.twig
git show f6932874e:$B/Resources/templates/partials/components/table_editable_cell.html.twig
git show f6932874e:$B/Common/TableEditable.php
git show f6932874e:$B/Common/EntityTableEditable.php
git show f6932874e:$B/Controller/Traits/TableEditableRestEntityController.php
git show f6932874e:project/templates/entities/invoice/partials/form_items_line.html.twig   # real usage, computed total
git show f6932874e:project/src/Wex/DemoBundle/Controller/Rest/TablesController.php        # session-backed demo
git log --format='%h %ci %s' 2ad342981^..cf6d62e8e -- project/src/Plugin/Accounting       # the 2018 birth, 30 commits
```

The descendant to compare with: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/front/pages/entity/invoice/tabs/edit/vue/items.vue` and `front/vue/entities-list-editable.vue` (Vue 2, PapaParse paste, unsaved-changes guard). Read only; never port Vue 2 code as is.

## What to recover

- The keyboard model: Escape, Enter down, Tab and Shift+Tab with wrap; waiting for a new row's id before moving on.
- The NEW row lifecycle: temporary id → server id, and a NEW row always present.
- The multi-line paste algorithm, including the creation of missing rows in one batch.
- Per-cell autosave (only when the value changed) as an option, next to "save all".
- Typed cell editors and the duration rule (7 h per day; it already exists as `DurationsHelper` in js-date).
- Hover and active styles, and the status line.

## Proposed modern shape (suggestion)

- An `editable` mode of `data-table`, not a new component. It would reuse the same column definitions, adding per column `editor` (text, number, money, duration, select, or none), `compute(row)` for derived columns (a function, **not** `eval`) and `required`.
- Two saving strategies chosen by prop: `cell` (one PATCH per changed cell, queued) and `batch` (a dirty set plus one save, with an unsaved-changes guard).
- A server contract in `symfony-api`: PATCH one field, POST a new row with partial fields, batch upsert (index → id), DELETE. Editable fields are **whitelisted** by the entity's API definition.
- Paste: TSV, plus CSV through a small parser, with a preview of how many rows will be created when that number is large.
- Accessibility: grid role, roving tabindex, `aria-live` for the save status.
- The server-rendered variant (`data-table.html.twig`) stays read-only.

## Bugs in the old code (do not port)

- `TableEditableRestEntityController::tableEditableEntityEditBatch()` uses the class-name string as the new entity, so batch creation breaks.
- The demo `editBatch` reads the wrong key and returns no ids.
- `// TODO ERROR WITH FIRST ITEM` on paste.
- The `Eval` cell runs `eval()` on template text.
- Any field name posted is set on the entity.

## Open questions

1. A mode of `data-table`, or a separate `data-grid` component?
2. Per-cell autosave, batch save, or both? The prod invoice editor chose batch plus a guard.
3. Is spreadsheet paste wanted in the generic component, or only in the invoice editor?
4. Should virtualisation (long lists) be in scope from the start?

## Decision (2026-10-13, owner)

Built, on the entity collection pipe rather than beside it: `components/collection/grid/abstract-entity-grid` extends `abstract-entity-table` and writes through the entity's repository — a cell left changed is an `update` of that field alone, the row typed at the foot a `create`, a row removed a `delete`, a pasted or uploaded table (columns named by the reader, previewed) one `postEntities('import')`. Columns: `editor` (text, number, money, select, checkbox), `scale`, `currency`, `options`, `required`, `default`, `compute(values)`. Keys: Enter down the column (creates on the new row), Escape restores, Tab native. `data-table` gained the `cell:<key>` and `append-row` slots. Value reading in `js/Helper/GridHelper.ts`, tested.

Answers to the questions: a component of its own extending the entity table (1); per-cell save only (2); paste generic (3); no virtualisation (4). First consumer: `symfony-accounting-ds` `invoice-item-grid`, after symfony-accounting was aligned on the entity/DTO model.
