// A value a refresh just changed, lit a moment (shapes/changed): the class is
// set on the element itself, the animation started again if it was still
// running, and taken off once it has played — nothing for a framework to
// keep, nothing left lit if a refresh comes before the light is gone.
export type ChangedWay = 'up' | 'down' | null;

const CLASSES = ['is-changed', 'is-changed--up', 'is-changed--down'];

export function changedLight(element: Element, way: ChangedWay = null): void {
  element.classList.remove(...CLASSES);
  // Read once: the browser drops the old animation before the new one starts.
  void (element as HTMLElement).offsetWidth;
  element.classList.add('is-changed', ...(way ? [`is-changed--${way}`] : []));
  element.addEventListener('animationend', () => element.classList.remove(...CLASSES), { once: true });
}

// Which way a value went, from what it said to what it says: a figure up or
// down, a word changed.
export function changedWay(before: string, after: string): ChangedWay {
  const from = Number(before);
  const to = Number(after);

  return before !== '' && after !== '' && !Number.isNaN(from) && !Number.isNaN(to) && from !== to
    ? (to > from ? 'up' : 'down')
    : null;
}
