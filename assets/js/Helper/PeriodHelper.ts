// The contract of a page's period-nav (components/period-nav), for whatever
// follows it — a chart, a table, figures —: the nav announces its period
// (`PERIOD_CHANGE_EVENT`, bubbling to the document, with ISO bounds — `from`
// inclusive, `to` exclusive); what starts listening after it asks to be told
// again (`PERIOD_REQUEST_EVENT`), whichever of the two is ready first.

export const PERIOD_CHANGE_EVENT = 'period-change';

export const PERIOD_REQUEST_EVENT = 'period-request';

export type PeriodBounds = { from: string; to: string };

// The period followed: `onChange` told of it at once if the nav already
// announced it, then of each change — never twice of the same bounds.
// Returns what stops following it.
export function periodFollow(onChange: (period: PeriodBounds) => void): () => void {
  let current: PeriodBounds | null = null;

  const listener = (event: Event): void => {
    const { from, to } = (event as CustomEvent<PeriodBounds>).detail;

    if (current?.from === from && current?.to === to) {
      return;
    }

    current = { from, to };
    onChange(current);
  };

  document.addEventListener(PERIOD_CHANGE_EVENT, listener);
  document.dispatchEvent(new CustomEvent(PERIOD_REQUEST_EVENT));

  return () => document.removeEventListener(PERIOD_CHANGE_EVENT, listener);
}
