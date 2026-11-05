// What changed between two versions of a text, line by line — and, in a line
// changed, word by word —, as the text diff draws it (components/diff-text).
// The twin of src/Helper/DiffTextHelper.php, which the server diff follows:
// the same rows for the same two texts, a change to either is a change to
// both (tests/js/DiffTextHelper.test.ts, tests/Unit/Helper/DiffTextHelperTest.php).

export type DiffTextPart = { text: string; changed: boolean };

export type DiffTextLine = {
  type: 'same' | 'add' | 'remove';
  text: string;
  // Its number in the text before, in the text after; none on the side it is not in.
  old: number | null;
  new: number | null;
  // Word by word, for a line changed facing the line it changed from.
  parts?: DiffTextPart[];
};

// Unchanged lines folded away, how many, where they start in either text.
export type DiffTextFold = { type: 'fold'; count: number; old: number; new: number; lines: DiffTextLine[] };

export type DiffTextRow = DiffTextLine | DiffTextFold;

type Edit<T> = { type: 'same' | 'add' | 'remove'; a?: T; b?: T; ai?: number; bi?: number };

// The shortest edit from one sequence to the other (Myers), as the run of
// what is kept, removed and added; in a run of changes, what goes before what
// comes, so a line changed reads as its old form then its new.
export function diffTextSequence<T>(a: T[], b: T[]): Edit<T>[] {
  const n = a.length;
  const m = b.length;
  const max = n + m;
  const offset = max + 1;
  const v = new Int32Array(2 * max + 3);
  const trace: Int32Array[] = [];
  let found = false;

  for (let d = 0; d <= max && !found; d++) {
    trace.push(v.slice());

    for (let k = -d; k <= d; k += 2) {
      let x = k === -d || (k !== d && v[offset + k - 1] < v[offset + k + 1]) ? v[offset + k + 1] : v[offset + k - 1] + 1;
      let y = x - k;

      while (x < n && y < m && a[x] === b[y]) {
        x++;
        y++;
      }

      v[offset + k] = x;

      if (x >= n && y >= m) {
        found = true;
        break;
      }
    }
  }

  const edits: Edit<T>[] = [];
  let x = n;
  let y = m;

  for (let d = trace.length - 1; d >= 0; d--) {
    const previous = trace[d];
    const k = x - y;
    const previousK = k === -d || (k !== d && previous[offset + k - 1] < previous[offset + k + 1]) ? k + 1 : k - 1;
    const previousX = previous[offset + previousK];
    const previousY = previousX - previousK;

    while (x > previousX && y > previousY) {
      edits.push({ type: 'same', a: a[x - 1], b: b[y - 1], ai: x - 1, bi: y - 1 });
      x--;
      y--;
    }

    if (d > 0) {
      if (x === previousX) {
        edits.push({ type: 'add', b: b[y - 1], bi: y - 1 });
      } else {
        edits.push({ type: 'remove', a: a[x - 1], ai: x - 1 });
      }
    }

    x = previousX;
    y = previousY;
  }

  edits.reverse();

  // In each run of changes, its removals first, then its additions.
  const ordered: Edit<T>[] = [];
  let run: Edit<T>[] = [];
  const flush = () => {
    ordered.push(...run.filter((edit) => edit.type === 'remove'), ...run.filter((edit) => edit.type === 'add'));
    run = [];
  };

  edits.forEach((edit) => {
    if (edit.type === 'same') {
      flush();
      ordered.push(edit);
    } else {
      run.push(edit);
    }
  });
  flush();

  return ordered;
}

// A text as its lines; nothing is no line at all.
export function diffTextSplitLines(text: string | null | undefined): string[] {
  const value = String(text ?? '').replace(/\r\n?/g, '\n');

  return value === '' ? [] : value.split('\n');
}

// A line as its words, the space and the marks between them kept.
export function diffTextTokens(line: string): string[] {
  return line.match(/\s+|[\p{L}\p{N}_]+|[^\s\p{L}\p{N}_]/gu) ?? [];
}

function parts(edits: Edit<string>[], side: 'remove' | 'add'): DiffTextPart[] {
  const result: DiffTextPart[] = [];

  edits.forEach((edit) => {
    if (edit.type !== 'same' && edit.type !== side) {
      return;
    }

    const text = (side === 'remove' ? edit.a : edit.b) ?? edit.a ?? '';
    const changed = edit.type !== 'same';
    const last = result[result.length - 1];

    if (last && last.changed === changed) {
      last.text += text;
    } else {
      result.push({ text, changed });
    }
  });

  return result;
}

/**
 * Every line of the two texts, kept, removed or added, each with its numbers;
 * a line removed facing a line added in the same run is given its words,
 * the ones changed marked (`words`).
 */
export function diffTextLines(before: string | null | undefined, after: string | null | undefined, words: boolean = true): DiffTextLine[] {
  const lines: DiffTextLine[] = diffTextSequence(diffTextSplitLines(before), diffTextSplitLines(after)).map((edit) => ({
    type: edit.type,
    text: (edit.type === 'add' ? edit.b : edit.a) ?? '',
    old: edit.ai === undefined ? null : edit.ai + 1,
    new: edit.bi === undefined ? null : edit.bi + 1
  }));

  if (!words) {
    return lines;
  }

  // Each run of changes: its i-th removal faces its i-th addition.
  for (let start = 0; start < lines.length;) {
    if (lines[start].type === 'same') {
      start++;
      continue;
    }

    let end = start;

    while (end < lines.length && lines[end].type !== 'same') {
      end++;
    }

    const removed = lines.slice(start, end).filter((line) => line.type === 'remove');
    const added = lines.slice(start, end).filter((line) => line.type === 'add');

    for (let i = 0; i < Math.min(removed.length, added.length); i++) {
      const edits = diffTextSequence(diffTextTokens(removed[i].text), diffTextTokens(added[i].text));

      removed[i].parts = parts(edits, 'remove');
      added[i].parts = parts(edits, 'add');
    }

    start = end;
  }

  return lines;
}

/**
 * The lines to read: the changes, and as many lines kept around each as
 * `context` says; the kept lines further away folded, how many said. A
 * context below zero folds nothing.
 */
export function diffTextRows(before: string | null | undefined, after: string | null | undefined, context: number = 3, words: boolean = true): DiffTextRow[] {
  const lines = diffTextLines(before, after, words);

  if (context < 0) {
    return lines;
  }

  const near = lines.map(() => false);

  lines.forEach((line, index) => {
    if (line.type !== 'same') {
      for (let i = Math.max(0, index - context); i <= Math.min(lines.length - 1, index + context); i++) {
        near[i] = true;
      }
    }
  });

  const rows: DiffTextRow[] = [];
  let folded: DiffTextLine[] = [];
  const flush = () => {
    if (folded.length) {
      rows.push({ type: 'fold', count: folded.length, old: folded[0].old ?? 0, new: folded[0].new ?? 0, lines: folded });
      folded = [];
    }
  };

  lines.forEach((line, index) => {
    if (line.type === 'same' && !near[index]) {
      folded.push(line);
    } else {
      flush();
      rows.push(line);
    }
  });
  flush();

  return rows;
}

// How many lines were added and removed.
export function diffTextStats(rows: DiffTextRow[]): { added: number; removed: number } {
  const lines = rows.filter((row): row is DiffTextLine => row.type !== 'fold');

  return {
    added: lines.filter((line) => line.type === 'add').length,
    removed: lines.filter((line) => line.type === 'remove').length
  };
}

/**
 * The rows side by side: a kept line on both sides, a run of changes its
 * removals on the left facing its additions on the right, the shorter side
 * left blank; a fold across both.
 */
export function diffTextSideBySide(rows: DiffTextRow[]): Array<{ fold?: DiffTextFold; left: DiffTextLine | null; right: DiffTextLine | null }> {
  const result: Array<{ fold?: DiffTextFold; left: DiffTextLine | null; right: DiffTextLine | null }> = [];
  let removed: DiffTextLine[] = [];
  let added: DiffTextLine[] = [];
  const flush = () => {
    for (let i = 0; i < Math.max(removed.length, added.length); i++) {
      result.push({ left: removed[i] ?? null, right: added[i] ?? null });
    }

    removed = [];
    added = [];
  };

  rows.forEach((row) => {
    if (row.type === 'remove') {
      removed.push(row);
    } else if (row.type === 'add') {
      added.push(row);
    } else {
      flush();
      result.push(row.type === 'fold' ? { fold: row, left: null, right: null } : { left: row, right: row });
    }
  });
  flush();

  return result;
}
