export type FilterOption = {
  value: string;
  label: string;
  count?: number | null;
};

export type FilterDefinition = {
  key: string;
  label: string;
  options: FilterOption[];
  // Several values at once, or one replacing the other.
  multiple?: boolean;
};

export type FilterValues = Record<string, string | string[] | null | undefined>;

// What a filter holds, as a list whatever its kind: a single choice is a list
// of one, an empty filter an empty list.
export function filterSelected(values: FilterValues, key: string): string[] {
  const value = values?.[key];

  if (value === null || value === undefined || value === '') {
    return [];
  }

  return (Array.isArray(value) ? value : [value]).map(String);
}

/**
 * The values once `value` is flipped in the filter `definition`: added to or
 * removed from a multiple one, chosen or cleared in a single one. A filter
 * left empty is dropped rather than kept as an empty list, so what is sent to
 * an api carries only what narrows it.
 */
export function filterToggle(values: FilterValues, definition: FilterDefinition, value: string): FilterValues {
  const selected = filterSelected(values, definition.key);
  const next = { ...values };
  let kept: string[];

  if (definition.multiple) {
    kept = selected.includes(value) ? selected.filter((entry) => entry !== value) : [...selected, value];
  } else {
    kept = selected.includes(value) ? [] : [value];
  }

  if (!kept.length) {
    delete next[definition.key];
  } else {
    next[definition.key] = definition.multiple ? kept : kept[0];
  }

  return next;
}

export function filterClear(values: FilterValues, key: string): FilterValues {
  const next = { ...values };
  delete next[key];

  return next;
}

/**
 * What the button of a filter says: its name while it narrows nothing, then
 * what it narrows to — the one value, or the first and how many more.
 */
export function filterSummary(definition: FilterDefinition, values: FilterValues): string {
  const selected = filterSelected(values, definition.key);

  if (!selected.length) {
    return definition.label;
  }

  const first = definition.options.find((option) => String(option.value) === selected[0]);
  const shown = first?.label ?? selected[0];
  const more = selected.length > 1 ? ` +${selected.length - 1}` : '';

  return `${definition.label}: ${shown}${more}`;
}

/**
 * Whether a row passes every filter holding something, reading the row's field
 * of the filter's key: equal to a chosen value, or — for a field holding a
 * list — holding one of them. For a table filtering what it already has; one
 * fed by an api lets the api do it.
 */
export function filterMatches(row: Record<string, unknown>, values: FilterValues): boolean {
  return Object.keys(values ?? {}).every((key) => {
    const selected = filterSelected(values, key);

    if (!selected.length) {
      return true;
    }

    const field = row?.[key];
    const held = Array.isArray(field) ? field.map(String) : [String(field ?? '')];

    return held.some((entry) => selected.includes(entry));
  });
}
