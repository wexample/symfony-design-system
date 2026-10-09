// A figure in the page's locale — the language the document is written in,
// unless one is named —, `digits` decimals at most. A value that is no number,
// « 124/75 », is given back as it came.
export function numberFormat(value: unknown, digits: number = 2, locale?: string | null): string {
  if (typeof value !== 'number' && (typeof value !== 'string' || value.trim() === '' || isNaN(Number(value)))) {
    return value === null || value === undefined ? '' : String(value);
  }

  const resolved = (locale || (typeof document !== 'undefined' ? document.documentElement.lang : '') || undefined)?.replace('_', '-');

  return new Intl.NumberFormat(resolved, { maximumFractionDigits: digits }).format(Number(value));
}

// An amount held in the minor unit of its currency — cents of a euro, none of
// a yen —, in the page's locale with its currency: what the `price` filter of
// symfony-money writes on the server side.
export function numberFormatMoney(minor: unknown, currencyCode: string, locale?: string | null): string {
  if (minor === null || minor === undefined || minor === '' || isNaN(Number(minor))) {
    return '';
  }

  const resolved = (locale || (typeof document !== 'undefined' ? document.documentElement.lang : '') || undefined)?.replace('_', '-');
  const formatter = new Intl.NumberFormat(resolved, { style: 'currency', currency: currencyCode });
  const decimals = formatter.resolvedOptions().maximumFractionDigits ?? 2;

  return formatter.format(Number(minor) / 10 ** decimals);
}

// How many decimals a step asks for: `0.05` two, `1` none — what a value taken
// by that step is shown with.
export function numberStepDigits(step: number | string | null | undefined): number {
  const text = String(step ?? '');
  const point = text.indexOf('.');

  return point === -1 ? 0 : text.length - point - 1;
}

// A figure as a person writes or copies it — « 1,5 », « 1 234,50 »,
// « 1.234,5 », « 1,234.5 » — read as a number, or null when it is none. The
// last of a comma or a point is the decimal one; spaces and the other mark
// only group the thousands.
export function numberParse(text: string): number | null {
  let value = text.trim().replace(/[\s  ']/g, '');
  const decimal = Math.max(value.lastIndexOf(','), value.lastIndexOf('.'));

  if (decimal !== -1) {
    value = value.slice(0, decimal).replace(/[.,]/g, '') + '.' + value.slice(decimal + 1);
  }

  return /^[-+]?(\d+\.?\d*|\.\d+)$/.test(value) ? Number(value) : null;
}
