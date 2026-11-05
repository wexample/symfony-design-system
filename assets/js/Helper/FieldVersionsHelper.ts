// The earlier versions of a text field, as its address answers them
// (`versionsUrl` of `textarea_input.schema.json` and `code_input.schema.json`):
// read once, shaped once, for whoever draws them.

// A version: its words — when, by whom — and the content itself.
export type FieldVersion = { label: string; content: string; date?: string | null; author?: string | null };

/**
 * Versions as an address answers them: a list, under `data` or `items` when
 * the answer wraps it, each with a label and a content; what has neither is
 * left out.
 */
export function fieldVersionsNormalize(raw: unknown): FieldVersion[] {
  const list = Array.isArray(raw)
    ? raw
    : (raw && typeof raw === 'object' ? ((raw as any).data ?? (raw as any).items ?? []) : []);

  if (!Array.isArray(list)) {
    return [];
  }

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
