// A colour as a form holds it — `#rrggbb`, lowercase —, read the same way by
// both faces of the colour field (components/form/color-input).

const COLOR_HEX = /^#?([0-9a-f]{3}|[0-9a-f]{6})$/i;

/**
 * What is typed, as a colour: `#abc`, `abc`, `#AABBCC` all `#aabbcc`;
 * nothing typed is nothing (`''`); anything else is not a colour (`null`).
 */
export function colorNormalize(text: unknown): string | null {
  const value = String(text ?? '').trim();

  if (value === '') {
    return '';
  }

  const match = COLOR_HEX.exec(value);

  if (!match) {
    return null;
  }

  const hex = match[1].toLowerCase();

  return `#${hex.length === 3 ? hex.split('').map((digit) => digit + digit).join('') : hex}`;
}

// The colours a field offers, as it is given them: `#hex`, or `{ value, label }`.
export function colorSwatches(raw: unknown): { value: string; label: string }[] {
  if (!Array.isArray(raw)) {
    return [];
  }

  return raw.flatMap((item) => {
    const isObject = typeof item === 'object' && item !== null;
    const value = colorNormalize(isObject ? item.value : item);

    return value ? [{ value, label: String((isObject ? item.label : null) ?? value) }] : [];
  });
}
