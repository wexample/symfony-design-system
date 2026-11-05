// The earlier versions of a text field, as its address answers them
// (`versionsUrl` of `textarea_input.schema.json` and `code_input.schema.json`):
// read once, shaped once, for whoever draws them.

// A version: its words — when, by whom — and the content itself.
export type FieldVersion = { label: string; content: string; date?: string | null; author?: string | null };

// The list an answer holds: itself, or under `data` or `items` — the one in
// the other where the api wraps a collection (`{ data: { items } }`).
export function fieldVersionsList(raw: unknown): unknown[] {
  let list = raw;

  for (let depth = 0; depth < 2 && list && typeof list === 'object' && !Array.isArray(list); depth++) {
    list = (list as any).data ?? (list as any).items ?? [];
  }

  return Array.isArray(list) ? list : [];
}

/**
 * Versions as an address answers them: a list, wrapped or not, each with a
 * label and a content; what has neither is left out.
 */
export function fieldVersionsNormalize(raw: unknown): FieldVersion[] {
  const list = fieldVersionsList(raw);

  return list
    .filter((item) => item && typeof item === 'object')
    .map((item) => ({
      label: String(item.label ?? '').trim() || '—',
      content: String(item.content ?? ''),
      date: item.date ?? null,
      author: item.author ?? null
    }));
}

/**
 * The versions an address answers, asked as the page's own request. An
 * address that fails answers none: the bar stays away rather than breaking.
 */
export async function fieldVersionsFetch(url: string, signal?: AbortSignal): Promise<unknown> {
  try {
    const response = await fetch(url, {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      signal
    });

    return response.ok ? await response.json() : [];
  } catch {
    return [];
  }
}
