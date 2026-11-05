// What a field of tags does, written once for both its faces — the vue and
// the server field's script (components/form/tags-input) —, so the two read
// what is typed, offer words and ask a source the same way.

// A word offered: what the tag holds, and what the list shows of it.
export type TagsSuggestion = { value: string; label: string };

// At most this many words offered at once: a list to pick from, not to read.
export const TAGS_SUGGESTIONS_LIMIT = 8;

// How long the typing pauses before a source is asked.
export const TAGS_SUGGESTIONS_DELAY_MS = 200;

// The words typed, split by the separator, trimmed, each once.
export function tagsSplit(text: string, separator: string = ','): string[] {
  return tagsUnique(String(text ?? '').split(separator));
}

export function tagsUnique(tags: unknown[]): string[] {
  return [...new Set(tags.map((tag) => String(tag ?? '').trim()).filter(Boolean))];
}

// The tags as one text, as the field posts them.
export function tagsJoin(tags: string[], separator: string = ','): string {
  return tags.join(`${separator} `);
}

/**
 * Words offered, as a field is given them or a source answers: a list of
 * words, or of `{ value, label }` — under `data` or `items` when the answer
 * wraps them.
 */
export function tagsNormalizeSuggestions(raw: unknown): TagsSuggestion[] {
  const list = Array.isArray(raw)
    ? raw
    : (raw && typeof raw === 'object' ? ((raw as any).data ?? (raw as any).items ?? []) : []);

  if (!Array.isArray(list)) {
    return [];
  }

  const seen = new Set<string>();

  return list.flatMap((item) => {
    const value = String(typeof item === 'object' && item !== null ? (item.value ?? '') : (item ?? '')).trim();
    const label = String(typeof item === 'object' && item !== null ? (item.label ?? value) : value).trim() || value;

    if (!value || seen.has(value)) {
      return [];
    }

    seen.add(value);

    return [{ value, label }];
  });
}

/**
 * The words to offer: those not taken yet, holding what is being typed —
 * by their word or their label, whatever the case —, the ones starting with
 * it first.
 */
export function tagsFilterSuggestions(
  suggestions: TagsSuggestion[],
  taken: string[],
  typed: string,
  limit: number = TAGS_SUGGESTIONS_LIMIT
): TagsSuggestion[] {
  const needle = String(typed ?? '').trim().toLowerCase();
  const held = new Set(taken);
  const matches = suggestions.filter(({ value, label }) => !held.has(value)
    && (!needle || value.toLowerCase().includes(needle) || label.toLowerCase().includes(needle)));
  const starts = (suggestion: TagsSuggestion) => (suggestion.value.toLowerCase().startsWith(needle)
    || suggestion.label.toLowerCase().startsWith(needle) ? 0 : 1);

  return matches
    .map((suggestion, index) => ({ suggestion, index }))
    .sort((a, b) => starts(a.suggestion) - starts(b.suggestion) || a.index - b.index)
    .slice(0, limit)
    .map(({ suggestion }) => suggestion);
}

// The fixed words and those a source answered, each once, the fixed first.
export function tagsMergeSuggestions(...lists: TagsSuggestion[][]): TagsSuggestion[] {
  return tagsNormalizeSuggestions(lists.flat());
}

// Where a source is asked for what is typed: `q` added to its address.
export function tagsSuggestionsUrl(url: string, typed: string): string {
  return `${url}${url.includes('?') ? '&' : '?'}q=${encodeURIComponent(String(typed ?? '').trim())}`;
}

/**
 * The words a source offers for what is typed, asked as the page's own
 * request (its cookies with it). A source that fails offers nothing: the
 * field still takes what is typed.
 */
export async function tagsFetchSuggestions(url: string, typed: string, signal?: AbortSignal): Promise<TagsSuggestion[]> {
  try {
    const response = await fetch(tagsSuggestionsUrl(url, typed), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      signal
    });

    return response.ok ? tagsNormalizeSuggestions(await response.json()) : [];
  } catch {
    return [];
  }
}
