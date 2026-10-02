// A row whose only action is a link opens it wherever it is pressed: the row
// is the thing, and its one link is where the thing is. The link itself stays
// in the row — reached with the keyboard, read by a screen reader, opening a
// modal or a panel as it would — and a press on the row is a press on it.
// What can be pressed on its own inside the row — a box, a button, another
// link — keeps its own press, and so does a press that selected text.
const INTERACTIVE = 'a, button, input, select, textarea, label, summary, [role="button"]';

export const ROW_LINK_SELECTOR = '.table--icon-link';

// The link a table's `row_link` adds, which wins over the row's other links.
export const ROW_OPEN_SELECTOR = '.table--row-open';

export function rowLinkClick(event: MouseEvent): void {
  const target = event.target as HTMLElement | null;
  const row = target?.closest<HTMLElement>('.table--row--link');

  if (!row || target.closest(INTERACTIVE) || window.getSelection()?.toString()) {
    return;
  }

  (row.querySelector<HTMLElement>(ROW_OPEN_SELECTOR) ?? row.querySelector<HTMLElement>(ROW_LINK_SELECTOR))?.click();
}
