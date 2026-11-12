// Pointing out a moment what just answered (`shapes/_flash.scss`): a zone that
// took what was picked elsewhere, showing it already or now. Flashed again
// while it still fades, it starts over.
const FLASH_MS = 900;
const FLASH_CLASS = 'flash';

const timers = new WeakMap<HTMLElement, number>();

export function flash(el: HTMLElement | null | undefined): void {
  if (!el) {
    return;
  }

  window.clearTimeout(timers.get(el));
  el.classList.remove(FLASH_CLASS);
  // Read once, so the class put back starts the fade over.
  void el.offsetWidth;
  el.classList.add(FLASH_CLASS);

  timers.set(el, window.setTimeout(() => {
    el.classList.remove(FLASH_CLASS);
    timers.delete(el);
  }, FLASH_MS));
}
