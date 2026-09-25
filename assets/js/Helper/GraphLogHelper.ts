export type GraphLogItem = {
  id: string;
  // The items this one comes from, first parent first. None for a root.
  parents?: string[];
  [key: string]: unknown;
};

// One segment of a lane between the middle of a row and the middle of the
// next: from the column it leaves to the column it reaches, in the colour of
// the lane it belongs to.
export type GraphLogEdge = {
  from: number;
  to: number;
  color: number;
};

export type GraphLogRow = {
  // The column the item's node stands in, and its lane's colour.
  column: number;
  color: number;
  // Lines reaching the node from the row above, coming from their column —
  // a lane that ends here, merged in or started here.
  incoming: GraphLogEdge[];
  // Lines leaving the row's middle for the next row.
  outgoing: GraphLogEdge[];
  // How many columns the row needs, for the width of its drawing.
  width: number;
};

type Lane = {
  // The item the lane is waiting for, and the colour it wears.
  expects: string;
  color: number;
};

/**
 * Lanes for a list of items read newest first, each pointing at its parents —
 * the drawing `git log --graph` makes. An item takes the lane that was waiting
 * for it (the leftmost, when several were: they merge into it); its lane then
 * waits for its first parent, and each other parent opens a lane of its own
 * unless one is already waiting for it. Lanes are never reordered, so a line
 * keeps its column as long as nothing ends to its left, and each lane keeps
 * its colour from start to end.
 */
export function graphLogLayout(items: GraphLogItem[]): GraphLogRow[] {
  const lanes: Array<Lane | null> = [];
  const rows: GraphLogRow[] = [];
  let nextColor = 0;

  const freeColumn = (): number => {
    const index = lanes.indexOf(null);

    return index === -1 ? lanes.length : index;
  };

  items.forEach((item) => {
    const before = lanes.map((lane) => (lane ? { ...lane } : null));
    const waiting = before
      .map((lane, index) => (lane && lane.expects === item.id ? index : -1))
      .filter((index) => index !== -1);

    let column: number;
    let color: number;

    if (waiting.length) {
      column = waiting[0];
      color = before[column]!.color;
    } else {
      // Nothing waited for it: a head, starting a lane of its own.
      column = freeColumn();
      color = nextColor++;
    }

    const incoming: GraphLogEdge[] = waiting.map((from) => ({ from, to: column, color: before[from]!.color }));

    // The lanes that waited for it end here, but the one it stands in.
    waiting.slice(1).forEach((index) => {
      lanes[index] = null;
    });

    const parents = item.parents ?? [];

    if (parents.length) {
      lanes[column] = { expects: parents[0], color };
    } else {
      lanes[column] = null;
    }

    const opened: Array<{ column: number; color: number }> = [];

    parents.slice(1).forEach((parent) => {
      if (lanes.some((lane) => lane?.expects === parent)) {
        const index = lanes.findIndex((lane) => lane?.expects === parent);
        opened.push({ column: index, color: lanes[index]!.color });

        return;
      }

      const index = freeColumn();
      const lane = { expects: parent, color: nextColor++ };
      lanes[index] = lane;
      opened.push({ column: index, color: lane.color });
    });

    // What leaves the row: every lane still running goes straight down from
    // where it is, but the node's own, which leaves the node, and those a
    // merge opened, which leave the node towards their column.
    const outgoing: GraphLogEdge[] = [];

    lanes.forEach((lane, index) => {
      if (!lane) {
        return;
      }

      const fromNode = index === column || opened.some((entry) => entry.column === index && !before[index]);

      if (index === column && parents.length) {
        outgoing.push({ from: column, to: column, color: lane.color });
      } else if (fromNode) {
        outgoing.push({ from: column, to: index, color: lane.color });
      } else if (opened.some((entry) => entry.column === index)) {
        // A merge towards a lane already running: the lane goes on, and the
        // node joins it.
        outgoing.push({ from: index, to: index, color: lane.color });
        outgoing.push({ from: column, to: index, color: lane.color });
      } else {
        outgoing.push({ from: index, to: index, color: lane.color });
      }
    });

    // Lanes running past the row without touching it come in from above too.
    before.forEach((lane, index) => {
      if (lane && lane.expects !== item.id && lanes[index] && lanes[index]!.expects === lane.expects) {
        incoming.push({ from: index, to: index, color: lane.color });
      }
    });

    // The trailing empty columns are dropped, so the drawing narrows again.
    while (lanes.length && lanes[lanes.length - 1] === null) {
      lanes.pop();
    }

    const width = Math.max(
      column + 1,
      ...incoming.map((edge) => Math.max(edge.from, edge.to) + 1),
      ...outgoing.map((edge) => Math.max(edge.from, edge.to) + 1)
    );

    rows.push({ column, color, incoming, outgoing, width });
  });

  return rows;
}

// The lanes' colours, taken from the categorical palette in an order that keeps
// neighbours apart, and reused past the last one.
const LANE_COLORS = [
  'blue-jeans', 'grass', 'bittersweet', 'lavender', 'mint', 'sunflower',
  'ruby', 'aqua', 'plum', 'straw', 'teal', 'pink-rose', 'grapefruit',
];

// The size of a lane and of a row, in pixels: the row's height is fixed by the
// stylesheet to the same, so the lines of two rows meet.
export const GRAPH_LOG_COLUMN = 14;
export const GRAPH_LOG_ROW = 28;

const laneColor = (index: number): string => `var(--color-cat-${LANE_COLORS[index % LANE_COLORS.length]}-fill)`;

const segment = (edge: GraphLogEdge, fromY: number, toY: number): string => {
  const x1 = (edge.from + 0.5) * GRAPH_LOG_COLUMN;
  const x2 = (edge.to + 0.5) * GRAPH_LOG_COLUMN;
  const path = x1 === x2
    ? `M${x1} ${fromY}L${x2} ${toY}`
    : `M${x1} ${fromY}C${x1} ${(fromY + toY) / 2} ${x2} ${(fromY + toY) / 2} ${x2} ${toY}`;

  return `<path d="${path}" style="stroke:${laneColor(edge.color)}"/>`;
};

/**
 * The drawing of one row: the lines reaching its middle from above, those
 * leaving it downwards, and the item's node on top. One markup string, drawn
 * alike by the server twin's script and by the vue.
 */
export function graphLogSvg(row: GraphLogRow, columns: number = row.width): string {
  const width = Math.max(columns, 1) * GRAPH_LOG_COLUMN;
  const middle = GRAPH_LOG_ROW / 2;
  const lines = [
    ...row.incoming.map((edge) => segment(edge, 0, middle)),
    ...row.outgoing.map((edge) => segment(edge, middle, GRAPH_LOG_ROW)),
  ].join('');
  const cx = (row.column + 0.5) * GRAPH_LOG_COLUMN;

  return `<svg class="graph-log--svg" width="${width}" height="${GRAPH_LOG_ROW}" viewBox="0 0 ${width} ${GRAPH_LOG_ROW}" aria-hidden="true">`
    + lines
    + `<circle class="graph-log--node" cx="${cx}" cy="${middle}" r="4" style="fill:${laneColor(row.color)}"/>`
    + '</svg>';
}

/**
 * The widest row of the list: every row is drawn this wide, so the text beside
 * the graph starts in one column.
 */
export function graphLogColumns(rows: GraphLogRow[]): number {
  return rows.reduce((widest, row) => Math.max(widest, row.width), 1);
}
