// How tall a tile of a wall stands (css/shapes/_wall.scss): its rows are a
// twelfth of a column wide, so a picture keeps its ratio by spanning as many
// twelfths as its height takes. The wall template works it out the same way.
export const WALL_ROWS_PER_COLUMN = 12;

// Neither a sliver nor a tower: past these ratios the picture is cropped.
export const WALL_RATIO_MIN = 0.5;
export const WALL_RATIO_MAX = 2;

// The rows a picture of width × height spans; square when its size is not
// known.
export function wallSpan(width?: number | null, height?: number | null): number {
  const ratio = width && height ? height / width : 1;
  const bounded = Math.min(WALL_RATIO_MAX, Math.max(WALL_RATIO_MIN, ratio));

  return Math.round(bounded * WALL_ROWS_PER_COLUMN);
}
