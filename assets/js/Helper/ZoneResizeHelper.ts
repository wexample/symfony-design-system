import App from '@wexample/symfony-loader/js/Class/App';
import KeyboardService from '@wexample/symfony-loader/js/Services/KeyboardService';
import { dragAxis, DragAxis } from '@wexample/js-helpers/Helper/Drag';

// What a region may not be dragged below, and what its neighbours keep whatever
// the pointer asks for: a region dragged out of existence leaves a handle
// nobody can find again.
const SIZE_MIN = 80;
const NEIGHBOUR_ROOM = 120;

// One press of an arrow key. Large enough to be worth pressing, small enough to
// aim with.
const KEYBOARD_STEP = 16;

// Makes a handle size the region it stands in. Written once and used by both
// twins of the component: the whole of it is reading the DOM and writing a
// custom property, which a vue does exactly like a script-mounted component.
//
// Returns the function that detaches it.
export function attachZoneResize(handle: HTMLElement, app: App): () => void {
  let split: HTMLElement | undefined;

  // The region this handle sizes is the one the split sizes: the nearest zone
  // above it that a split holds directly. Walking up rather than taking the
  // parent, because a handle written inside a component is a grandchild of the
  // region once that component has been mounted.
  const resolveZone = (): HTMLElement | undefined => {
    let el: HTMLElement | null = handle.parentElement;

    while (el) {
      const parent = el.parentElement;

      if (el.classList.contains('zone') && parent?.classList.contains('zone--split')) {
        split = parent;
        return el;
      }

      el = parent;
    }

    return undefined;
  };

  const zone = resolveZone();

  if (!zone || !split) {
    return () => {};
  }

  const axis: DragAxis = (handle.dataset.zoneResizerAxis as DragAxis)
    || (split.classList.contains('zone--split--vertical') ? 'y' : 'x');
  const direction: 1 | -1 = handle.dataset.zoneResizerEdge === 'start' ? -1 : 1;

  // The axis is known here and not when the markup was written: the handle is
  // placed and announced once the split it belongs to has been read.
  handle.classList.toggle('zone-resizer--y', axis === 'y');
  handle.setAttribute('aria-orientation', axis === 'y' ? 'horizontal' : 'vertical');

  const currentSize = (): number => (axis === 'x' ? zone.offsetWidth : zone.offsetHeight);
  const maxSize = (): number =>
    (axis === 'x' ? split!.clientWidth : split!.clientHeight) - NEIGHBOUR_ROOM;

  const applySize = (size: number): void => {
    zone.style.setProperty('--zone-size', `${Math.round(size)}px`);
  };

  const persistSize = (size: number | null): void => {
    const id = zone.dataset.zoneId;

    if (id) {
      app.persistUiState(`ui.layout.zone.${id}.size`, size === null ? null : Math.round(size));
    }
  };

  const detachDrag = dragAxis({
    handle,
    axis,
    direction,
    start: currentSize,
    min: () => SIZE_MIN,
    max: maxSize,
    onMove: applySize,
    onEnd: persistSize,
  });

  // Back to the size the page gave it. A region dragged somewhere unusable is
  // the one case where the pointer needs a way out that is not the pointer.
  const onDoubleClick = (event: Event): void => {
    event.preventDefault();
    zone.style.removeProperty('--zone-size');
    persistSize(null);
  };

  handle.addEventListener('dblclick', onDoubleClick);

  const onKey = (way: number): void => {
    const size = Math.min(
      Math.max(currentSize() + way * direction * KEYBOARD_STEP, SIZE_MIN),
      maxSize()
    );

    applySize(size);
    persistSize(size);
  };

  // Through the keyboard service, and only while the handle itself has the
  // focus: the arrows belong to whatever the page is doing until then.
  const keyboard = app.getServiceOrFail(KeyboardService) as KeyboardService;
  const keys: [string, number][] = axis === 'x'
    ? [[KeyboardService.KEY_ARROW_LEFT, -1], [KeyboardService.KEY_ARROW_RIGHT, 1]]
    : [[KeyboardService.KEY_ARROW_UP, -1], [KeyboardService.KEY_ARROW_DOWN, 1]];

  keys.forEach(([key, way]) => {
    keyboard.registerKeyDown(handle, key, () => onKey(way), {
      enabled: () => document.activeElement === handle,
      preventDefault: true,
    });
  });

  return (): void => {
    detachDrag();
    handle.removeEventListener('dblclick', onDoubleClick);
    keyboard.unregisterOwner(handle);
  };
}
