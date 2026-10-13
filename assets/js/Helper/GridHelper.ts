import { numberParse } from './NumberHelper';

// A column of an editable grid, as far as its values go: `editor` names the
// kind of value — `text`, `number`, `money`, `select`, `checkbox` —, `scale`
// what a number is stored multiplied by (a quantity kept ×100), `currency` the
// one a money amount is held in the minor unit of, `options` what a select
// offers, `required` a value a new row cannot go without.
export type GridColumn = {
  key: string;
  label?: string;
  editor?: string;
  scale?: number;
  currency?: string;
  options?: { value: unknown; label: string }[];
  required?: boolean;
};

// A table waiting for its columns: the rows as they came, the column each one
// feeds (a key, or '' to leave it), and whether the first row names them.
export type GridTable = {
  rows: string[][];
  columns: string[];
  header: boolean;
};

export type GridRecord = {
  row: number;
  values: Record<string, unknown>;
  invalid: string[];
};

const TRUE_WORDS = ['1', 'true', 'yes', 'y', 'oui', 'x', '✓', '✔'];
const FALSE_WORDS = ['', '0', 'false', 'no', 'n', 'non'];

function gridLocale(locale?: string | null): string | undefined {
  return (locale || (typeof document !== 'undefined' ? document.documentElement.lang : '') || undefined)?.replace('_', '-');
}

function gridComparable(text: unknown): string {
  return String(text ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[\s  %]/g, '').toLowerCase();
}

// How many decimals the minor unit of a currency holds: two for a euro, none
// for a yen.
export function gridCurrencyDigits(currency: string): number {
  return new Intl.NumberFormat('en', { style: 'currency', currency }).resolvedOptions().maximumFractionDigits ?? 2;
}

// What a stored value reads as in its input: a scaled number or an amount in
// minor units as the figure a person types, in the page's locale but without
// grouping, which an input would only have to read back.
export function gridValueToText(value: unknown, column: GridColumn, locale?: string | null): string {
  if (value === null || value === undefined || value === '') {
    return '';
  }

  if (column.editor === 'number' || column.editor === 'money') {
    const digits = column.editor === 'money' ? gridCurrencyDigits(column.currency || 'EUR') : null;
    const divisor = digits === null ? (column.scale ?? 1) : 10 ** digits;

    return new Intl.NumberFormat(gridLocale(locale), {
      maximumFractionDigits: digits ?? 6,
      minimumFractionDigits: digits ?? 0,
      useGrouping: false,
    }).format(Number(value) / divisor);
  }

  return String(value);
}

// What a typed or pasted text is stored as, `{ value }` — null for nothing —,
// or null when it cannot be one: « 1 234,50 € » is a money amount, « 20 % » a
// number, a select takes an option's value or its label.
export function gridTextToValue(text: unknown, column: GridColumn, locale?: string | null): { value: unknown } | null {
  const typed = String(text ?? '').trim();

  if (column.editor === 'checkbox') {
    const word = typed.toLowerCase();

    if (TRUE_WORDS.includes(word)) {
      return { value: true };
    }

    return FALSE_WORDS.includes(word) ? { value: false } : null;
  }

  if (typed === '') {
    return { value: null };
  }

  if (column.editor === 'number' || column.editor === 'money') {
    // What surrounds the figure — a currency, a percent sign — is no part of it.
    const figure = numberParse(typed.replace(/[^\d.,'\-+\s  ]/g, ''), locale);

    if (figure === null) {
      return null;
    }

    const factor = column.editor === 'money' ? 10 ** gridCurrencyDigits(column.currency || 'EUR') : (column.scale ?? 1);

    return { value: Math.round(figure * factor) };
  }

  if (column.editor === 'select') {
    const options = column.options ?? [];
    const wanted = gridComparable(typed);
    const option = options.find((candidate) => gridComparable(candidate.value) === wanted)
      ?? options.find((candidate) => gridComparable(candidate.label) === wanted);

    if (option) {
      return { value: option.value };
    }

    // A figure, scaled as the options are: « 20 » for a rate kept as 2000.
    const figure = numberParse(typed.replace(/[^\d.,'\-+\s  ]/g, ''), locale);
    const scaled = figure === null ? null : Math.round(figure * (column.scale ?? 1));
    const matching = options.find((candidate) => scaled !== null && Number(candidate.value) === scaled);

    return matching ? { value: matching.value } : null;
  }

  return { value: typed };
}

// Cells copied out of a spreadsheet arrive as tab separated lines. More than
// one cell is a table; a single one is only text.
export function gridPasteRows(text: string): string[][] | null {
  if (!text.includes('\t') && !text.trim().includes('\n')) {
    return null;
  }

  return text.replace(/\r/g, '').split('\n')
    .filter((row) => row.trim() !== '')
    .map((row) => row.split('\t'));
}

// Each column of a table given the field it most likely feeds. A first row
// naming the columns says it, by their labels or keys; otherwise they are
// taken in the grid's order. The first row is a header when it names them,
// or when a figure the grid needs is no figure there but is one below.
export function gridTableGuess(rows: string[][], columns: GridColumn[], locale?: string | null): GridTable {
  const width = Math.max(0, ...rows.map((row) => row.length));
  const first = rows[0] ?? [];
  const named = first.map((cell) => {
    const wanted = gridComparable(cell);

    return wanted ? columns.find((column) => gridComparable(column.label) === wanted || gridComparable(column.key) === wanted)?.key ?? '' : '';
  });

  if (rows.length > 1 && named.some((key) => key)) {
    return { rows, columns: Array.from({ length: width }, (_, index) => named[index] ?? ''), header: true };
  }

  const guessed = Array.from({ length: width }, (_, index) => columns[index]?.key ?? '');
  const figures = columns.filter((column) => column.required && (column.editor === 'number' || column.editor === 'money'));
  const header = rows.length > 1 && figures.some((column) => {
    const index = guessed.indexOf(column.key);

    return index !== -1
      && gridTextToValue(first[index], column, locale) === null
      && gridTextToValue(rows[1][index], column, locale) !== null;
  });

  return { rows, columns: guessed, header };
}

// The rows of a table read as values of the grid, header aside, and each
// one's cells that could not be read — or that a required field left empty.
// A row whose chosen cells are all blank is no row.
export function gridTableRead(table: GridTable, columns: GridColumn[], locale?: string | null): GridRecord[] {
  const chosen = table.columns
    .map((key, index) => ({ index, column: columns.find((column) => column.key === key) }))
    .filter((entry) => entry.column);
  const records: GridRecord[] = [];

  table.rows.forEach((row, rowIndex) => {
    if ((table.header && rowIndex === 0) || chosen.every(({ index }) => String(row[index] ?? '').trim() === '')) {
      return;
    }

    const values: Record<string, unknown> = {};
    const invalid: string[] = [];

    for (const { index, column } of chosen) {
      if (column.key in values) {
        continue;
      }

      const read = gridTextToValue(row[index], column, locale);

      if (read === null) {
        invalid.push(column.key);
      } else if (read.value !== null) {
        values[column.key] = read.value;
      }
    }

    for (const column of columns) {
      if (column.required && !(column.key in values) && !invalid.includes(column.key)) {
        invalid.push(column.key);
      }
    }

    records.push({ row: rowIndex, values, invalid });
  });

  return records;
}
