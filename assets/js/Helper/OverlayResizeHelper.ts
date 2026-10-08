import App from '@wexample/symfony-loader/js/Class/App';
import { dragAxis, DragAxis } from '@wexample/js-helpers/Helper/Drag';
import { uiStateGet, uiStateSet } from './UiStateHelper';

// What a box may not be dragged below, and the share of the window it may not
// pass: a box dragged out of reach leaves an edge nobody can find again.
const WIDTH_MIN = 320;
const HEIGHT_MIN = 200;
const WINDOW_SHARE = 0.96;

type Edge = 'start' | 'end' | 'top' | 'bottom';

const EDGES: [Edge, DragAxis, 1 | -1][] = [
  ['start', 'x', -1],
  ['end', 'x', 1],
  ['top', 'y', -1],
  ['bottom', 'y', 1],
];

// Makes a box centred in the window — a modal — sized by its edges. Nothing is
// drawn: each edge is a strip the pointer finds by its cursor. The box stays
// centred, so an edge pulled grows it on both sides at once — what is
// followed is half the size, and the size written is twice that.
//
// The size is the box's `--overlay-width` and `--overlay-height`. Given a key
// (`stateKey`), it is remembered as a resized zone is, and given back the next
// time the box opens. A double click on an edge forgets it.
//
// Returns the function that detaches it.
export function attachOverlayResize(box: HTMLElement, app: App, stateKey: string | null = null): () => void {
  const property = (axis: DragAxis): string => (axis === 'x' ? '--overlay-width' : '--overlay-height');
  const stateName = (axis: DragAxis): string | null =>
    stateKey ? `ui.overlay.${stateKey}.${axis === 'x' ? 'width' : 'height'}` : null;

  const apply = (axis: DragAxis, size: number | null): void => {
    if (size === null) {
      box.style.removeProperty(property(axis));
    } else {
      box.style.setProperty(property(axis), `${Math.round(size)}px`);
    }
  };

  const persist = (axis: DragAxis, size: number | null): void => {
    const key = stateName(axis);

    if (key) {
      uiStateSet(app, key, size === null ? null : Math.round(size));
    }
  };

  (['x', 'y'] as DragAxis[]).forEach((axis) => {
    const key = stateName(axis);
    const remembered = key ? uiStateGet<number | null>(app, key, null) : null;

    if (remembered) {
      apply(axis, remembered);
    }
  });

  const detachers = EDGES.map(([edge, axis, direction]) => {
    const handle = document.createElement('div');
    handle.className = `overlay-resizer overlay-resizer--${edge}`;
    handle.setAttribute('aria-hidden', 'true');
    box.appendChild(handle);

    const current = (): number => (axis === 'x' ? box.offsetWidth : box.offsetHeight);
    const windowSize = (): number => (axis === 'x' ? window.innerWidth : window.innerHeight);

    const detachDrag = dragAxis({
      handle,
      axis,
      // A page read from the right puts its start on the right: the edge
      // named `start` is then pulled the other way.
      direction: axis === 'x' && getComputedStyle(box).direction === 'rtl' ? (direction === 1 ? -1 : 1) : direction,
      start: () => current() / 2,
      min: () => (axis === 'x' ? WIDTH_MIN : HEIGHT_MIN) / 2,
      max: () => (windowSize() * WINDOW_SHARE) / 2,
      onMove: (half) => apply(axis, half * 2),
      onEnd: (half) => persist(axis, half * 2),
    });

    const onDoubleClick = (event: Event): void => {
      event.preventDefault();
      apply(axis, null);
      persist(axis, null);
    };

    handle.addEventListener('dblclick', onDoubleClick);

    return (): void => {
      detachDrag();
      handle.removeEventListener('dblclick', onDoubleClick);
      handle.remove();
    };
  });

  return (): void => detachers.forEach((detach) => detach());
}
