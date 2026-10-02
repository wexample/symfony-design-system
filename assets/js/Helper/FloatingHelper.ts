export type FloatingSide = 'top' | 'bottom' | 'left' | 'right';

export type FloatingOptions = {
  // The side asked for. The other one on the same axis is taken when it does
  // not fit, and whichever side has the most room when neither does.
  placement?: FloatingSide;
  // Room between the anchor and what floats beside it.
  offset?: number;
  // Room kept between what floats and the edges of the window.
  margin?: number;
  // Above or below, where the box lines up with the anchor: its middle, or
  // the anchor's start edge — a list under its field begins where it does.
  align?: 'center' | 'start';
};

export type FloatingPosition = {
  side: FloatingSide;
  left: number;
  top: number;
  // Where the arrow meets the floating box, along its edge facing the anchor:
  // the anchor's middle, kept on the box.
  arrow: number;
};

const OPPOSITE: Record<FloatingSide, FloatingSide> = {
  top: 'bottom',
  bottom: 'top',
  left: 'right',
  right: 'left',
};

// Keeps the arrow off the rounded corners.
const ARROW_INSET = 10;

const clamp = (value: number, min: number, max: number): number => Math.min(Math.max(value, min), Math.max(min, max));

/**
 * Where a box floats beside an anchor: on the side asked for, on the other
 * side when the window ends first, slid along that side so it stays in the
 * window, its arrow still pointing at the anchor. Measured in the window's
 * coordinates, for a box positioned `fixed`. Shared by what points at
 * something — a tooltip, a step of a tour.
 */
export function floatingCompute(
  anchorRect: DOMRect,
  size: { width: number; height: number },
  options: FloatingOptions = {}
): FloatingPosition {
  const offset = options.offset ?? 8;
  const margin = options.margin ?? 8;
  const viewport = { width: window.innerWidth, height: window.innerHeight };

  const room: Record<FloatingSide, number> = {
    top: anchorRect.top - offset - margin,
    bottom: viewport.height - anchorRect.bottom - offset - margin,
    left: anchorRect.left - offset - margin,
    right: viewport.width - anchorRect.right - offset - margin,
  };
  const needs = (side: FloatingSide): number => (side === 'top' || side === 'bottom' ? size.height : size.width);
  const fits = (side: FloatingSide): boolean => room[side] >= needs(side);

  const asked = options.placement ?? 'top';
  let side: FloatingSide = asked;

  if (!fits(asked)) {
    side = fits(OPPOSITE[asked])
      ? OPPOSITE[asked]
      : (Object.keys(room) as FloatingSide[]).reduce((best, candidate) => (room[candidate] > room[best] ? candidate : best), asked);
  }

  let left: number;
  let top: number;
  let arrow: number;

  if (side === 'top' || side === 'bottom') {
    const center = anchorRect.left + anchorRect.width / 2;
    const rtl = document.dir === 'rtl' || document.documentElement.dir === 'rtl';
    const wanted = options.align === 'start'
      ? (rtl ? anchorRect.right - size.width : anchorRect.left)
      : center - size.width / 2;
    left = clamp(wanted, margin, viewport.width - size.width - margin);
    top = side === 'top' ? anchorRect.top - offset - size.height : anchorRect.bottom + offset;
    arrow = clamp(center - left, ARROW_INSET, size.width - ARROW_INSET);
  } else {
    const middle = anchorRect.top + anchorRect.height / 2;
    top = clamp(middle - size.height / 2, margin, viewport.height - size.height - margin);
    left = side === 'left' ? anchorRect.left - offset - size.width : anchorRect.right + offset;
    arrow = clamp(middle - top, ARROW_INSET, size.height - ARROW_INSET);
  }

  return { side, left: Math.round(left), top: Math.round(top), arrow: Math.round(arrow) };
}

/**
 * Places `floating` beside `anchor`: position, the side it ended on as
 * `data-side`, and the arrow's place as `--floating-arrow`. The box has to be
 * displayed — even transparent — for its size to be read.
 */
export function floatingPlace(anchor: Element, floating: HTMLElement, options: FloatingOptions = {}): FloatingPosition {
  const position = floatingCompute(
    anchor.getBoundingClientRect(),
    { width: floating.offsetWidth, height: floating.offsetHeight },
    options
  );

  floating.style.left = `${position.left}px`;
  floating.style.top = `${position.top}px`;
  floating.style.setProperty('--floating-arrow', `${position.arrow}px`);
  floating.dataset.side = position.side;

  return position;
}
