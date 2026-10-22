// Copies a text to the clipboard, and says whether it got there: the page may
// not be allowed to — permission refused — and a button claiming it copied
// would then be lying.
//
// The clipboard API exists only in a secure context. Served over plain http —
// a development host —, the text is copied the older way: selected in a field
// of its own, out of sight, and copied as a selection is.
export async function clipboardCopy(text: string): Promise<boolean> {
  if (navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(text);

      return true;
    } catch {
      // Refused: the older way may still be allowed.
    }
  }

  return clipboardCopyBySelection(text);
}

function clipboardCopyBySelection(text: string): boolean {
  const field = document.createElement('textarea');
  const focused = document.activeElement as HTMLElement | null;

  field.value = text;
  field.setAttribute('readonly', '');
  field.style.position = 'fixed';
  field.style.opacity = '0';
  field.style.pointerEvents = 'none';
  document.body.appendChild(field);
  field.select();

  let copied = false;

  try {
    copied = document.execCommand('copy');
  } catch {
    copied = false;
  }

  field.remove();
  // Where the visitor was, they stay: the keyboard goes on from the button.
  focused?.focus({ preventScroll: true });

  return copied;
}
