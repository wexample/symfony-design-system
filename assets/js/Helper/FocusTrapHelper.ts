// What keeps the keyboard inside a dialog while it is open, and gives it back
// where it was taken from once it closes. A dialog is modal to the pointer
// through its backdrop; without this it is not to the keyboard, and Tab walks
// on into the page hidden behind it.

const FOCUSABLE = [
  'a[href]',
  'area[href]',
  'button:not([disabled])',
  'input:not([disabled]):not([type="hidden"])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  'iframe',
  '[contenteditable="true"]',
  '[tabindex]',
].join(',');

type Focusable = {
  tabIndex: number;
};

// What Tab can reach inside `root`, in document order: shown, not taken out of
// the tab order.
export function focusTrapFocusables(root: HTMLElement): HTMLElement[] {
  return Array.from(root.querySelectorAll<HTMLElement>(FOCUSABLE)).filter(
    (element) => element.tabIndex >= 0 && element.getClientRects().length > 0 && !element.closest('[inert]')
  );
}

/**
 * Where Tab goes when it would leave the dialog: from the last element to the
 * first, Shift+Tab from the first to the last, and from anything outside — the
 * dialog itself included — to the first or the last. Null when the browser
 * may move on by itself, inside.
 */
export function focusTrapNext<T extends Focusable>(
  focusables: T[],
  active: T | null,
  backwards: boolean
): T | null {
  if (!focusables.length) {
    return null;
  }

  const first = focusables[0];
  const last = focusables[focusables.length - 1];
  const index = active ? focusables.indexOf(active) : -1;

  if (index === -1) {
    return backwards ? last : first;
  }

  if (backwards && active === first) {
    return last;
  }

  if (!backwards && active === last) {
    return first;
  }

  return null;
}

// The element focus goes back to: the one it was taken from, while it is still
// in the page and can hold it.
export function focusTrapCanReturn(element: Element | null): element is HTMLElement {
  return element instanceof HTMLElement
    && element.isConnected
    && element !== document.body
    && !element.closest('[inert]');
}
