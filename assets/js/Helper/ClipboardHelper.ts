// Copies a text to the clipboard, and says whether it got there: the page may
// not be allowed to — no secure context, permission refused — and a button
// claiming it copied would then be lying.
export async function clipboardCopy(text: string): Promise<boolean> {
  try {
    await navigator.clipboard.writeText(text);

    return true;
  } catch {
    return false;
  }
}
