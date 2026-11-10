// What a thread works out of its lines alone, written once for whoever draws
// one: which line goes on from the one before — the same author, soon after —,
// and where a day begins.

// How long after the line before a line of the same author still goes on
// from it rather than starting again.
export const THREAD_CONTINUED_MS = 5 * 60 * 1000;

export type ThreadLineFacts = { author: string; own: boolean; date: string | number | Date | null };

function threadTime(date: ThreadLineFacts['date']): number | null {
  if (date === null || date === undefined || date === '') {
    return null;
  }

  const time = new Date(date).getTime();

  return Number.isNaN(time) ? null : time;
}

// Whether a line goes on from the one before it: the same author, the same
// side, written within `windowMs`. A line with no date never does.
export function threadContinued(previous: ThreadLineFacts | null, line: ThreadLineFacts, windowMs: number = THREAD_CONTINUED_MS): boolean {
  if (!previous || previous.author !== line.author || previous.own !== line.own) {
    return false;
  }

  const before = threadTime(previous.date);
  const now = threadTime(line.date);

  return before !== null && now !== null && now >= before && now - before <= windowMs;
}

// The day a line was written on, as the reader's clock has it: `YYYY-MM-DD`,
// null for a line with no date.
export function threadDayKey(date: ThreadLineFacts['date']): string | null {
  const time = threadTime(date);

  if (time === null) {
    return null;
  }

  const day = new Date(time);

  return `${day.getFullYear()}-${String(day.getMonth() + 1).padStart(2, '0')}-${String(day.getDate()).padStart(2, '0')}`;
}

// A day said as a reader says it: today, yesterday — the words given —, else
// the date in the page's language, the year only when it is not this one.
export function threadDayLabel(dayKey: string, words: { today: string; yesterday: string }, locale: string | undefined = undefined, now: Date = new Date()): string {
  const today = threadDayKey(now);
  const yesterday = threadDayKey(new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1));

  if (dayKey === today) {
    return words.today;
  }

  if (dayKey === yesterday) {
    return words.yesterday;
  }

  const [year, month, day] = dayKey.split('-').map(Number);
  const date = new Date(year, month - 1, day);

  return new Intl.DateTimeFormat(locale, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    ...(year === now.getFullYear() ? {} : { year: 'numeric' })
  }).format(date);
}
