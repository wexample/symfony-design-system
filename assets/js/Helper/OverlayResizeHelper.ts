import App from '@wexample/symfony-loader/js/Class/App';
import { dragAxis, DragAxis } from '@wexample/js-helpers/Helper/Drag';
import { uiStateGet, uiStateSet } from './UiStateHelper';

// What a box may not be dragged below, and the share of the window it may not
// pass: a box dragged out of reach leaves an edge nobody can find again.
const WIDTH_MIN = 320;
const HEIGHT_MIN = 200;
const WINDOW_SHARE = 0.96;

export type OverlayResizeEdge = 'start' | 'end' | 'top' | 'bottom';

const EDGES: Record<OverlayResizeEdge, [DragAxis, 1 | -1]> = {
  start: ['x', -1],
  end: ['x', 1],
  top: ['y', -1],
  bottom: ['y', 1],
};

export interface OverlayResizeOptions {
  // The edges the pointer may pull: all four of a box in the middle of the
  // window, the one facing the page of a box held against a side.
  edges: OverlayResizeEdge[];
  // Centred in the window, the box grows on both sides of an edge pulled: what
  // is followed is then half the size, and the size written twice that.
  centered: boolean;
  // Where the size is remembered, if anywhere.
  stateKey?: string | null;
}

// Makes an overlay's box — a modal, a panel — sized by its edges. Nothing is
// drawn: each edge is a strip the pointer finds by its cursor.
//
// The size is the box's `--overlay-width` and `--overlay-height`. Given a key
// (`stateKey`), it is remembered as a resized zone is, and given back the next
// time the box opens. A double click on an edge forgets it.
//
// Returns the function that detaches it.
export function attachOverlayResize(box: HTMLElement, app: App, options: OverlayResizeOptions): () => void {
  const stateKey = options.stateKey ?? null;
  const factor = options.centered ? 2 : 1;

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

  const detachers = options.edges.map((edge) => {
    const [axis, direction] = EDGES[edge];
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
      start: () => current() / factor,
      min: () => (axis === 'x' ? WIDTH_MIN : HEIGHT_MIN) / factor,
      max: () => (windowSize() * WINDOW_SHARE) / factor,
      onMove: (share) => apply(axis, share * factor),
      onEnd: (share) => persist(axis, share * factor),
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
