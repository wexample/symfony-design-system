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

// A figure as a person of the page's locale writes or copies it — « 1,5 »
// and « 1 234,50 » in French, « 1,234.5 » in English —, read as a number, or
// null when it is none, or when it could be read two ways. The locale says
// which mark is the decimal one: a comma groups thousands in English and
// splits decimals in French; a grouping mark is followed by three digits,
// or it is not one — « 72,5 » is then a decimal comma whatever the page.
// A point alone, « 1.5 », is decimal everywhere, as a number control reads
// it. « 1,234 » on an English page stays a thousand and more.
export function numberParse(text: string, locale?: string | null): number | null {
  const resolved = (locale || (typeof document !== 'undefined' ? document.documentElement.lang : '') || undefined)?.replace('_', '-');
  const decimal = new Intl.NumberFormat(resolved).formatToParts(1.5).find((part) => part.type === 'decimal')?.value === ',' ? ',' : '.';
  const group = decimal === ',' ? '.' : ',';
  let value = text.trim().replace(/[\s\u00a0\u202f']/g, '');

  if (decimal === ',' && !value.includes(',') && value.split('.').length === 2) {
    return /^[-+]?(\d+\.?\d*|\.\d+)$/.test(value) ? Number(value) : null;
  }

  // « 72,5 » on an English page: a comma followed by anything but three
  // digits groups nothing — it can only be a decimal one, from elsewhere.
  if (decimal === '.' && !value.includes('.') && /^[-+]?\d+,(\d{1,2}|\d{4,})$/.test(value)) {
    return Number(value.replace(',', '.'));
  }

  // « 72,5 » on an English page: a comma followed by anything but three
  // digits groups nothing — it can only be a decimal one, from elsewhere.
  if (decimal === '.' && !value.includes('.') && /^[-+]?\d+,(\d{1,2}|\d{4,})$/.test(value)) {
    return Number(value.replace(',', '.'));
  }

  const [whole, fraction, ...rest] = value.split(decimal);
  const grouped = new RegExp(`^[-+]?\\d{1,3}(\\${group}\\d{3})+$`);

  if (rest.length || (whole.includes(group) && !grouped.test(whole))
    || (fraction !== undefined && !/^\d+$/.test(fraction))) {
    return null;
  }

  value = whole.split(group).join('') + (fraction !== undefined ? `.${fraction}` : '');

  return /^[-+]?\d+(\.\d+)?$/.test(value) ? Number(value) : null;
}
