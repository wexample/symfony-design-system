// The place of a row's actions by what they are to it (`rel`, written by
// TableRowActionHelper.php): opening first, editing next, the row's own
// actions after, deleting last — so the eye and the pencil stand in the same
// column from one row to the next, however many actions follow. An action
// with no `rel` keeps the place it was given among the others.
const REL_RANKS: Record<string, number> = {
  open: 0,
  edit: 1,
  delete: 3,
};

const OTHER_RANK = 2;

export function sortActionsByRel<T extends { rel?: string | null }>(actions: T[]): T[] {
  const rank = (action: T): number => REL_RANKS[action.rel ?? ''] ?? OTHER_RANK;

  // Array.prototype.sort is stable: equal ranks keep the order they came in.
  return [...actions].sort((a, b) => rank(a) - rank(b));
}
