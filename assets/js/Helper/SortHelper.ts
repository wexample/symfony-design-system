export type SortDirection = 'asc' | 'desc';

// What a list is sorted by: the key the rows are compared on — or the one an
// api is asked to sort by — and which way.
export type SortState = {
  key: string;
  direction: SortDirection;
};

export type SortAria = 'ascending' | 'descending' | 'none';

export function sortEquals(a: SortState | null | undefined, b: SortState | null | undefined): boolean {
  if (!a || !b) {
    return !a && !b;
  }

  return a.key === b.key && a.direction === b.direction;
}

/**
 * The order once the header of `key` is pressed: ascending, then descending,
 * then back to the default order — never to an order nobody declared. On the
 * column the default order already sorts, the press turns it the other way,
 * and the next one comes back to it. Null only when there is no default, the
 * rows then going back to the order they were given in.
 */
export function sortNext(current: SortState | null, key: string, defaultSort: SortState | null = null): SortState | null {
  const state = current ?? defaultSort;

  if (!state || state.key !== key) {
    return { key, direction: 'asc' };
  }

  if (sortEquals(state, defaultSort)) {
    return { key, direction: state.direction === 'asc' ? 'desc' : 'asc' };
  }

  return state.direction === 'asc' ? { key, direction: 'desc' } : defaultSort;
}

// The order as an api is asked for it: the key, a leading `-` when it runs
// down (`-createdAt`). Null asks for nothing, leaving the api its own order.
export function sortToQuery(state: SortState | null): string | null {
  if (!state) {
    return null;
  }

  return state.direction === 'desc' ? `-${state.key}` : state.key;
}

export function sortFromQuery(value: string | null | undefined): SortState | null {
  if (!value) {
    return null;
  }

  return value.startsWith('-')
    ? { key: value.slice(1), direction: 'desc' }
    : { key: value, direction: 'asc' };
}

// What `aria-sort` says of the header of `key`.
export function sortAria(state: SortState | null, key: string): SortAria {
  if (!state || state.key !== key) {
    return 'none';
  }

  return state.direction === 'asc' ? 'ascending' : 'descending';
}

// Nothing to sort on: such a row goes last whichever way the list runs, so a
// list sorted on its latest date does not open on every row that has none.
export function sortIsEmpty(value: unknown): boolean {
  return value === null
    || value === undefined
    || (typeof value === 'string' && value.trim() === '')
    || (typeof value === 'number' && Number.isNaN(value))
    || (value instanceof Date && Number.isNaN(value.getTime()));
}

// The field `path` of a row, dots reaching into what it holds.
export function sortValueAt(row: unknown, path: string): unknown {
  return path.split('.').reduce<unknown>(
    (value, part) => (value === null || value === undefined ? undefined : (value as Record<string, unknown>)[part]),
    row
  );
}

/**
 * Two values neither of which is empty: numbers and dates as quantities,
 * anything else as words of the locale — accents and case where the language
 * puts them, so « Élodie » sits with the E's, and figures inside a text read as
 * numbers, so « Room 9 » comes before « Room 10 ».
 */
export function sortCompareValues(a: unknown, b: unknown, collator: Intl.Collator): number {
  const left = a instanceof Date ? a.getTime() : (typeof a === 'boolean' ? Number(a) : a);
  const right = b instanceof Date ? b.getTime() : (typeof b === 'boolean' ? Number(b) : b);

  if (typeof left === 'number' && typeof right === 'number') {
    return left - right;
  }

  return collator.compare(String(left), String(right));
}

export function sortCollator(locale?: string | null): Intl.Collator {
  return new Intl.Collator(locale ? locale.replace('_', '-') : undefined, { numeric: true });
}

export type SortApplyOptions = {
  // The locale words are compared in, a server one (`fr_FR`) or a BCP 47 tag.
  locale?: string | null;
  // What a row is compared on; its field of the state's key when left out.
  value?: (row: unknown, key: string) => unknown;
  // A row that stays where it is and parts the list in runs, each sorted on its
  // own: a group heading keeps the rows it names under it.
  isFixed?: (row: unknown) => boolean;
};

/**
 * The rows in the order `state` asks for, as a new list: rows comparing equal
 * keep the order they came in, and empty values go last both ways.
 */
export function sortApply<Row>(rows: Row[], state: SortState | null, options: SortApplyOptions = {}): Row[] {
  if (!state) {
    return [...rows];
  }

  const collator = sortCollator(options.locale);
  const read = options.value ?? ((row: unknown, key: string) => sortValueAt(row, key));
  const sign = state.direction === 'desc' ? -1 : 1;
  const compare = (a: Row, b: Row): number => {
    const left = read(a, state.key);
    const right = read(b, state.key);
    const leftEmpty = sortIsEmpty(left);
    const rightEmpty = sortIsEmpty(right);

    if (leftEmpty || rightEmpty) {
      return Number(leftEmpty) - Number(rightEmpty);
    }

    return sign * sortCompareValues(left, right, collator);
  };

  const sorted: Row[] = [];
  let run: Row[] = [];

  rows.forEach((row) => {
    if (options.isFixed?.(row)) {
      sorted.push(...run.sort(compare), row);
      run = [];
    } else {
      run.push(row);
    }
  });

  return [...sorted, ...run.sort(compare)];
}
