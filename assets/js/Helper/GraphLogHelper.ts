import type { GraphLogLaneColor } from '@wexample/js-graph-log/Helper/GraphLog';

// The lanes' colours, taken from the categorical palette in an order that keeps
// neighbours apart, and reused past the last one. The layout and the drawing
// are @wexample/js-graph-log's; the colours are the design system's.
const LANE_COLORS = [
  'blue-jeans', 'grass', 'bittersweet', 'lavender', 'mint', 'sunflower',
  'ruby', 'aqua', 'plum', 'straw', 'teal', 'pink-rose', 'grapefruit',
];

export const graphLogLaneColor: GraphLogLaneColor = (index) =>
  `var(--cat-${LANE_COLORS[index % LANE_COLORS.length]}-9)`;
