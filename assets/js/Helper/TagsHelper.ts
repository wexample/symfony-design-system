// What a field of tags does, written once for both its faces — the vue and
// the server field's script (components/form/tags-input) —, so the two read
// what is typed, offer words and ask a source the same way.

import { type Option, OPTIONS_SOURCE_DELAY_MS, optionsFetch, optionsFilter, optionsMerge, optionsNormalize, optionsSourceUrl } from './OptionsHelper';

// A word offered: what the tag holds, and what the list shows of it.
export type TagsSuggestion = Option;

// At most this many words offered at once: a list to pick from, not to read.
export const TAGS_SUGGESTIONS_LIMIT = 8;

// How long the typing pauses before a source is asked.
export const TAGS_SUGGESTIONS_DELAY_MS = OPTIONS_SOURCE_DELAY_MS;

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

// Words offered, as a field is given them or a source answers (OptionsHelper).
export function tagsNormalizeSuggestions(raw: unknown): TagsSuggestion[] {
  return optionsNormalize(raw);
}

// The words to offer: those not taken yet, holding what is being typed, the
// ones starting with it first.
export function tagsFilterSuggestions(
  suggestions: TagsSuggestion[],
  taken: string[],
  typed: string,
  limit: number = TAGS_SUGGESTIONS_LIMIT
): TagsSuggestion[] {
  return optionsFilter(suggestions, typed, taken, limit);
}

// The fixed words and those a source answered, each once, the fixed first.
export function tagsMergeSuggestions(...lists: TagsSuggestion[][]): TagsSuggestion[] {
  return optionsMerge(...lists);
}

// Where a source is asked for what is typed: `q` added to its address.
export function tagsSuggestionsUrl(url: string, typed: string): string {
  return optionsSourceUrl(url, typed);
}

// The words a source offers for what is typed; a source that fails offers
// nothing, the field still takes what is typed.
export function tagsFetchSuggestions(url: string, typed: string, signal?: AbortSignal): Promise<TagsSuggestion[]> {
  return optionsFetch(url, typed, signal);
}
