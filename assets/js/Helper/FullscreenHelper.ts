// An element of the page taken to the whole screen and back — a stage, a
// chart, a viewer —, through the browser's Fullscreen API. One element at a
// time holds the screen: taking another gives the first back.

// Whether the browser lets the page take the screen at all.
export function fullscreenIsAvailable(): boolean {
  return typeof document !== 'undefined' && Boolean(document.fullscreenEnabled);
}

// Whether `el` is the one holding the screen.
export function fullscreenIsActive(el: Element | null): boolean {
  return Boolean(el) && document.fullscreenElement === el;
}

// `el` taken to the whole screen, or given it back when it holds it.
export async function fullscreenToggle(el: Element | null): Promise<void> {
  if (!el || !fullscreenIsAvailable()) {
    return;
  }

  if (fullscreenIsActive(el)) {
    await document.exitFullscreen();

    return;
  }

  await el.requestFullscreen();
}

// Told each time an element takes the screen or gives it back — Escape
// included, which the browser handles on its own. Returns what stops it.
export function fullscreenOnChange(listener: () => void): () => void {
  document.addEventListener('fullscreenchange', listener);

  return () => document.removeEventListener('fullscreenchange', listener);
}
