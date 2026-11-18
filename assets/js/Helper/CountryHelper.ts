// A country by its ISO 3166-1 alpha-2 code, as a page shows it: its flag —
// the two regional indicator letters its code spells — and its name in the
// page's language (the twin of twig's `country_name`).

export function countryFlag(code: string): string {
  return /^[A-Z]{2}$/.test(code)
    ? String.fromCodePoint(...[...code].map((letter) => 0x1f1e6 + letter.charCodeAt(0) - 65))
    : '';
}

export function countryName(code: string, locale: string = document.documentElement.lang || 'en'): string {
  try {
    return new Intl.DisplayNames([locale.replace('_', '-')], { type: 'region' }).of(code) ?? code;
  } catch {
    return code;
  }
}
