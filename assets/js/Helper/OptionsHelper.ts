// Options read from a source as a field asks for them — the words a tags
// field offers, the choices of a select searched —: written once, so every
// field reading a source reads it the same way.

// An option: what the field holds, and what is shown of it.
export type Option = { value: string; label: string };

// How long the typing pauses before a source is asked.
export const OPTIONS_SOURCE_DELAY_MS = 200;

/**
 * Options as a field is given them or a source answers: a list of words, or
 * of `{ value, label }` — under `data` or `items` when the answer wraps
 * them, the one in the other where the api wraps a collection —, each value
 * once.
 */
export function optionsNormalize(raw: unknown): Option[] {
  let list = raw;

  for (let depth = 0; depth < 2 && list && typeof list === 'object' && !Array.isArray(list); depth++) {
    list = (list as any).data ?? (list as any).items ?? [];
  }

  if (!Array.isArray(list)) {
    return [];
  }

  const seen = new Set<string>();

  return list.flatMap((item) => {
    const isObject = typeof item === 'object' && item !== null;
    const value = String(isObject ? (item.value ?? '') : (item ?? '')).trim();
    const label = String(isObject ? (item.label ?? value) : value).trim() || value;

    if (!value || seen.has(value)) {
      return [];
    }

    seen.add(value);

    return [{ value, label }];
  });
}

/**
 * The options holding what is typed — by their value or their label,
 * whatever the case —, those starting with it first, the ones left out
 * (`taken`) dropped.
 */
export function optionsFilter(options: Option[], typed: string, taken: string[] = [], limit: number | null = null): Option[] {
  const needle = String(typed ?? '').trim().toLowerCase();
  const held = new Set(taken);
  const starts = (option: Option) => (option.value.toLowerCase().startsWith(needle)
    || option.label.toLowerCase().startsWith(needle) ? 0 : 1);
  const matches = options
    .filter(({ value, label }) => !held.has(value)
      && (!needle || value.toLowerCase().includes(needle) || label.toLowerCase().includes(needle)))
    .map((option, index) => ({ option, index }))
    .sort((a, b) => starts(a.option) - starts(b.option) || a.index - b.index)
    .map(({ option }) => option);

  return limit === null ? matches : matches.slice(0, limit);
}

// The field's own options and those a source answered, each once, the
// field's first.
export function optionsMerge(...lists: Option[][]): Option[] {
  return optionsNormalize(lists.flat());
}

// Where a source is asked for what is typed: `q` added to its address.
export function optionsSourceUrl(url: string, typed: string): string {
  return `${url}${url.includes('?') ? '&' : '?'}q=${encodeURIComponent(String(typed ?? '').trim())}`;
}

/**
 * The options a source answers for what is typed, asked as the page's own
 * request (its cookies with it). A source that fails answers nothing: the
 * field says so rather than breaking.
 */
export async function optionsFetch(url: string, typed: string, signal?: AbortSignal): Promise<Option[]> {
  try {
    const response = await fetch(optionsSourceUrl(url, typed), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      signal
    });

    return response.ok ? optionsNormalize(await response.json()) : [];
  } catch {
    return [];
  }
}

// The label of a value: the option holding it, else the value itself.
export function optionsLabelOf(options: Option[], value: string): string {
  return options.find((option) => option.value === value)?.label ?? value;
}
