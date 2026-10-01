# Sapiens — from a list to a record: modal, row actions, list states, keyboard

Opened: 2026-10-01
Updated: 2026-10-01
Author: agent:sapiens

## Context

Asked by the Sapiens app (`HOME_HABILIS/local/sapiens`). Its three admin surfaces (patients, healthcare-professional accounts, establishments) are the same pattern: a `data-table`, a double-click opens the record in a modal, the record has actions (save, deactivate, export…). Closes, in Sapiens' `.wex/knowledge/contributing/stack-requirements.md.j2`: **Opening a record as a modal on double-click**, **Row actions with confirmation**, **Empty, loading and error states of a list**, **Columns shown conditionally on the reader's role** (the rendering side only), **Keyboard accessibility of modals and tables, which IEC 62366 will be read against**.

## 1. Opening a record

- Double-click on a row opens the record in a `modal` — and so does **Enter** on a focused row, and a visible "open" action per row: double-click alone is invisible and unreachable by keyboard.
- The modal's URL is addressable (a route), so a record can be linked to and reloads in place.
- Closing returns focus to the row it was opened from.

## 2. Row actions with confirmation

- Destructive or irreversible actions (deactivate, archive, revoke) ask for confirmation through `confirm`, naming the record ("Deactivate Julien Gautier?").
- The confirmation's default button is the safe one; Escape cancels.
- After the action: `toast` feedback, the row updated in place without losing sort, filters, page or selection.

## 3. List states

- **Loading**: a state that keeps the layout (no jump), announced to screen readers.
- **Empty**: distinguish "nothing yet" (with the add action) from "nothing matches the filters" (with a reset-filters action).
- **Error**: a message and a retry, never a blank table.

## 4. Columns that vary

An application may pass a different column list to different readers. The table only has to render whatever columns it receives cleanly: no empty gaps, and a default sort whose column is absent falls back instead of failing. Which columns a reader gets is the application's business, decided on the server.

## 5. Keyboard and screen readers

- Table: rows reachable with arrow keys or Tab, the focused row visible, Enter opens, the row actions reachable.
- Modal: focus moves into it on open, is trapped inside, Escape closes, focus returns to the origin. `role="dialog"`, `aria-modal`, labelled by its title.
- Checked in the headless browser already used for column sorting.

## Tests

- Double-click, Enter and the open action all open the same record; closing restores focus.
- A destructive action without confirmation does nothing; with it, the row updates and sort/filters/page are kept.
- Empty-by-filter and empty-for-real render differently.
- A column absent from the data renders no gap.
- Focus is trapped in the modal and returns on close.

## Scope, as settled with the owner

The journey itself — the three admin screens, their routes, the record forms, the texts, which action deactivates what — is the application's. The package only adds what the journey cannot be built without. Done below; what stays the application's is in the reply.

## Already there, checked

- A record in a modal at a real route: an action `{ href, target: 'modal' }` opens the route in the modal layout; the same URL opened directly renders the full page (HTTP 200 on the demo's `modal-test-simple`).
- `confirm` service (Escape backs out), `toast` service, the loading state (spinner `role="status"`, layout kept while refreshing), a row patched in place by `rowKey`.

## Gaps found and filled

- **Modal / panel** (`AbstractOverlayPageManager`): no dialog semantics, focus left behind the overlay, Tab walking into the page, focus lost on close. Now `role="dialog"`, `aria-modal`, named by the first heading; focus moves onto the box on open, Tab / Shift+Tab trapped inside (`js/Helper/FocusTrapHelper.ts`), focus given back to the element it came from.
- **Confirm**: Enter answered with the primary action — on a destructive question, the dangerous one; no dialog semantics, no initial focus, Tab escaping. Now `role="alertdialog"` named by the title and described by the message; focus and Enter start on `defaultAction`, else on the backing-out action when one is `destructive`, else the primary; a focused answer keeps its own Enter; Tab trapped; focus returned. `destructive` is drawn `button--danger` (it had no style). **Default changed for existing callers**: a confirm holding a `destructive` action now defaults to cancel — the old default was unsafe.
- **data-table, opening a row**: an action marked `open: true` is pressed by a double-click or Enter on its row, both renderers; Vue also emits `row-activate` and takes `activatable`. Rows that open are keyboard-reachable: one in the tab order (roving), arrows / Home / End, visible focus ring.
- **data-table, states**: empty because of the filters, told apart from empty (label + "Clear the filters": a button in Vue, a link on the server, `QueryHelper::clearUrl`); an `empty` slot / option for the "add the first one" action; Vue `error` + `retry` in place of the rows (`role="alert"`). The post action button of a Vue row now carries its `aria-label`.
- Tests: `tests/js/FocusTrapHelper.test.ts` (4), `tests/js/PromptActionsHelper.test.ts` (4), 11 more in `tests/js/data-table.test.ts` (row opening, roving, arrows, filtered-empty vs empty, clear filters, row updated in place keeping order/page/selection, default order on an absent column), `QueryHelperTest::testClearUrlOnlyWhileAFilterHoldsSomething`. Mutation-checked on the confirm default, the trap wrap and the filtered-empty rule. 42 JS / 20 PHP.
- Demo: tables page — the eye of every Twig action table is `open: true`; the Vue selectable table is `activatable` and has a "Simulate a failed read"; the filters section shows the filtered-empty state. Confirm page — a destructive question.
- Knowledge: `usage/tables` — From a list to a record, States in place of the rows, Columns a reader may not see, Overlays.

## Verification

Headless Chrome (DevTools key events): Twig table — ArrowDown to the next row, Enter opens the modal, focus on `[role=dialog]` with `aria-modal="true"` named "Modal test - simple", Tab ×5 and Shift+Tab stay inside, Escape closes and focus is back on the row, double-click opens too. Vue table — Enter on a row emits `row-activate`, the error replaces the rows as an alert, Try again brings them back. Confirm — `alertdialog` named and described, focus on Cancel, Deactivate drawn `button--danger`, Tab stays inside, Enter on Cancel answers `cancel` and gives focus back to the button, Escape backs out. Twig filtered-empty state over HTTP. A first run caught Enter on a focused answer being swallowed (the keyboard service prevents default before the handler runs); fixed by deciding it in `enabled`.

## Reply

**Verdict: real gaps, implemented, with a demo — the journey itself stays the application's.** Commits `feat(overlays): …` / `feat(data-table): …` (see below) in `symfony-design-system` and `symfony-design-system-demo`.

**What the package does now.** Modals and panels are proper modal dialogs (role, name, focus in, trap, focus back). A confirm defaults to the safe answer beside a destructive one, traps and returns focus. A data-table row opens by double-click, Enter or its visible `open` action, and is reachable by Tab and the arrows. Lists tell "nothing matches the filters" (with a clear-filters action) from "nothing yet" (with the page's own add action), and a Vue list shows a read error with a retry. No bundle option. One default changed: a confirm with a `destructive` action now focuses and Enters on cancel.

> **For the Sapiens agent — from a list to a record**
>
> Nothing to configure. What is the package's: the dialog and keyboard behaviour above. What stays yours, per surface:
>
> - **Opening**: give the row an action `{ icon: 'ph:bold/eye', label: 'Open', href: <record route>, target: 'modal', open: true }` (Twig: in the actions cell; Vue: in the actions column, with `route`/`params`). Double-click, Enter and the visible eye then open the same route in a modal; closing returns focus to the row. Make the record route render the record (a full page when opened directly).
> - **Destructive actions**: call `confirm({ title, message, actions: [{ value: 'deactivate', label: 'Deactivate', role: 'destructive' }, { value: 'cancel', label: 'Cancel', role: 'secondary' }] })` naming the record in the title; act only on `'deactivate'`; then `toast` and hand the table its rows again with a `rowKey` — order, filters, page and selection are kept. The server must check the permission and the state itself.
> - **States**: pass `loading` while reading; on failure, `error` + `@retry` (Vue). Put your "add" action in the `empty` slot / option; the "nothing matches the filters" state is automatic once the table has `filters`.
> - **Columns by role**: build the column list *and* the row data on the server per role — never send a field the reader may not see. If a role cannot see the default-order column, give that role another default.
> - **Headings**: a record page in a modal must start with a heading (`h1`–`h3`) — it names the dialog.
>
> Open, belonging to other lines: the permission checks and audit trail of the actions (security / traceability lines).
