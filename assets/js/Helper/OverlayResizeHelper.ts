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

  const rtl = (): boolean => getComputedStyle(box).direction === 'rtl';
  // Which way the pointer goes for the box to grow, along the edge's axis. A
  // page read from the right puts its start on the right: the edge named
  // `start` is then pulled the other way.
  const sign = (edge: OverlayResizeEdge): 1 | -1 => {
    const [axis, direction] = EDGES[edge];

    return axis === 'x' && rtl() ? (direction === 1 ? -1 : 1) : direction;
  };
  const current = (axis: DragAxis): number => (axis === 'x' ? box.offsetWidth : box.offsetHeight);
  const clamp = (axis: DragAxis, size: number): number => Math.min(
    Math.max(size, axis === 'x' ? WIDTH_MIN : HEIGHT_MIN),
    (axis === 'x' ? window.innerWidth : window.innerHeight) * WINDOW_SHARE
  );

  const addHandle = (name: string): HTMLElement => {
    const handle = document.createElement('div');
    handle.className = `overlay-resizer overlay-resizer--${name}`;
    handle.setAttribute('aria-hidden', 'true');
    box.appendChild(handle);

    return handle;
  };

  // Back to the size the page gave it, on the axes the handle pulls.
  const resetOnDoubleClick = (handle: HTMLElement, axes: DragAxis[]): () => void => {
    const onDoubleClick = (event: Event): void => {
      event.preventDefault();
      axes.forEach((axis) => {
        apply(axis, null);
        persist(axis, null);
      });
    };

    handle.addEventListener('dblclick', onDoubleClick);

    return (): void => handle.removeEventListener('dblclick', onDoubleClick);
  };

  const edgeDetachers = options.edges.map((edge) => {
    const [axis] = EDGES[edge];
    const handle = addHandle(edge);

    const detachDrag = dragAxis({
      handle,
      axis,
      direction: sign(edge),
      start: () => current(axis) / factor,
      min: () => (axis === 'x' ? WIDTH_MIN : HEIGHT_MIN) / factor,
      max: () => ((axis === 'x' ? window.innerWidth : window.innerHeight) * WINDOW_SHARE) / factor,
      onMove: (share) => apply(axis, share * factor),
      onEnd: (share) => persist(axis, share * factor),
    });
    const detachReset = resetOnDoubleClick(handle, [axis]);

    return (): void => {
      detachDrag();
      detachReset();
      handle.remove();
    };
  });

  // Where an edge across meets an edge down, a corner pulls both at once.
  const xEdges = options.edges.filter((edge) => EDGES[edge][0] === 'x');
  const yEdges = options.edges.filter((edge) => EDGES[edge][0] === 'y');
  const cornerDetachers = yEdges.flatMap((yEdge) => xEdges.map((xEdge) => {
    const handle = addHandle(`${yEdge}-${xEdge}`);
    let gesture: { id: number; x: number; y: number; width: number; height: number; userSelect: string } | null = null;
    let width = 0;
    let height = 0;

    const onPointerMove = (event: PointerEvent): void => {
      if (gesture?.id !== event.pointerId) {
        return;
      }

      width = clamp('x', gesture.width + (event.clientX - gesture.x) * sign(xEdge) * factor);
      height = clamp('y', gesture.height + (event.clientY - gesture.y) * sign(yEdge) * factor);
      apply('x', width);
      apply('y', height);
    };

    const onPointerUp = (event: PointerEvent): void => {
      if (gesture?.id !== event.pointerId) {
        return;
      }

      handle.releasePointerCapture(event.pointerId);
      document.body.style.userSelect = gesture.userSelect;
      gesture = null;
      handle.removeEventListener('pointermove', onPointerMove);
      handle.removeEventListener('pointerup', onPointerUp);
      handle.removeEventListener('pointercancel', onPointerUp);
      persist('x', width);
      persist('y', height);
    };

    // The pointer is captured on the corner, so the gesture survives leaving
    // it, as an edge's does.
    const onPointerDown = (event: PointerEvent): void => {
      if (gesture || event.button !== 0) {
        return;
      }

      event.preventDefault();
      width = current('x');
      height = current('y');
      gesture = { id: event.pointerId, x: event.clientX, y: event.clientY, width, height, userSelect: document.body.style.userSelect };
      document.body.style.userSelect = 'none';
      handle.setPointerCapture(event.pointerId);
      handle.addEventListener('pointermove', onPointerMove);
      handle.addEventListener('pointerup', onPointerUp);
      handle.addEventListener('pointercancel', onPointerUp);
    };

    handle.addEventListener('pointerdown', onPointerDown);
    const detachReset = resetOnDoubleClick(handle, ['x', 'y']);

    return (): void => {
      handle.removeEventListener('pointerdown', onPointerDown);
      handle.removeEventListener('pointermove', onPointerMove);
      handle.removeEventListener('pointerup', onPointerUp);
      handle.removeEventListener('pointercancel', onPointerUp);
      detachReset();
      handle.remove();
    };
  }));

  return (): void => [...edgeDetachers, ...cornerDetachers].forEach((detach) => detach());
}
