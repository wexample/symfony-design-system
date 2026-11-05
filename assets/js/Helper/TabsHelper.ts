// How a bar of tabs fits the width it is given, written once for its two
// faces — the vue and the server bar's script (components/tab) —: its words
// while they fit; its icons alone where every tab has one and the words do
// not; past that, the bar scrolled sideways between two arrows, the tab lit
// brought into view.

// Markers on the bar (`.tabs`): the words set aside, the arrows shown.
export const TABS_ATTRIBUTE_ICONS = 'data-icons';
export const TABS_ATTRIBUTE_SCROLL = 'data-scroll';

// How much of the bar's width an arrow scrolls it by.
const TABS_SCROLL_SHARE = 0.8;

function tabsItems(bar: HTMLElement): HTMLElement[] {
  return [...bar.querySelectorAll<HTMLElement>(':scope > .tabs--item')];
}

function tabsOverflows(bar: HTMLElement): boolean {
  return bar.scrollWidth > bar.clientWidth + 1;
}

// Every tab holds an icon: the words may be set aside without a tab left
// blank.
export function tabsHaveIcons(bar: HTMLElement): boolean {
  const items = tabsItems(bar);

  return items.length > 0 && items.every((item) => item.querySelector('.tabs--item-icon'));
}

/**
 * The bar laid out for the room it has: words, else icons, else scrolled.
 * A tab whose word is set aside keeps it, said under the pointer and to a
 * reader (`data-tooltip`, `aria-label`).
 */
export function tabsFit(bar: HTMLElement): void {
  bar.removeAttribute(TABS_ATTRIBUTE_ICONS);
  bar.removeAttribute(TABS_ATTRIBUTE_SCROLL);

  if (tabsOverflows(bar) && tabsHaveIcons(bar)) {
    bar.setAttribute(TABS_ATTRIBUTE_ICONS, '');
  }

  if (tabsOverflows(bar)) {
    bar.setAttribute(TABS_ATTRIBUTE_SCROLL, '');
  }

  const iconsOnly = bar.hasAttribute(TABS_ATTRIBUTE_ICONS);

  tabsItems(bar).forEach((item) => {
    const label = item.querySelector('.tabs--item-label')?.textContent?.trim() ?? '';

    if (iconsOnly && label) {
      item.setAttribute('data-tooltip', label);
      item.setAttribute('aria-label', label);
    } else if (item.dataset.tabsLabelled !== 'own') {
      item.removeAttribute('data-tooltip');
      item.removeAttribute('aria-label');
    }
  });

  tabsUpdateArrows(bar);
  tabsRevealActive(bar);
}

// An arrow at an end the bar is already scrolled to has nowhere to go.
export function tabsUpdateArrows(bar: HTMLElement): void {
  const start = bar.querySelector<HTMLButtonElement>(':scope > .tabs--arrow--start');
  const end = bar.querySelector<HTMLButtonElement>(':scope > .tabs--arrow--end');
  const max = bar.scrollWidth - bar.clientWidth;
  // Read the way the bar runs: a right-to-left bar scrolls to negative values.
  const position = Math.abs(bar.scrollLeft);

  if (start) {
    start.disabled = position <= 1;
  }

  if (end) {
    end.disabled = position >= max - 1;
  }
}

// An arrow pressed: the bar scrolled most of its width that way.
export function tabsScrollBy(bar: HTMLElement, direction: -1 | 1): void {
  const rtl = getComputedStyle(bar).direction === 'rtl';

  bar.scrollBy({ left: direction * (rtl ? -1 : 1) * bar.clientWidth * TABS_SCROLL_SHARE, behavior: 'smooth' });
}

// The tab lit in view of a scrolled bar.
export function tabsRevealActive(bar: HTMLElement): void {
  if (!bar.hasAttribute(TABS_ATTRIBUTE_SCROLL)) {
    return;
  }

  bar.querySelector<HTMLElement>(':scope > .tabs--item--active')?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
}

/**
 * The bar kept fitting the room it is given: laid out again as what holds it
 * is resized, its arrows kept true as it scrolls. What it returns lets go.
 */
export function tabsWatch(bar: HTMLElement): () => void {
  let frame = 0;
  const fit = () => {
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(() => tabsFit(bar));
  };
  const onScroll = () => tabsUpdateArrows(bar);
  // The room is what holds the bar: its own width follows what it shows.
  const observer = typeof ResizeObserver === 'undefined' ? null : new ResizeObserver(fit);

  observer?.observe(bar.parentElement ?? bar);
  bar.addEventListener('scroll', onScroll, { passive: true });
  fit();

  return () => {
    cancelAnimationFrame(frame);
    observer?.disconnect();
    bar.removeEventListener('scroll', onScroll);
  };
}
