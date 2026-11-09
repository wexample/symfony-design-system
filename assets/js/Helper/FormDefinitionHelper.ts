import { tagsJoin, tagsSplit, tagsUnique } from './TagsHelper';
import { fieldExpressionMatches } from './FieldExpressionHelper';
import AdaptiveService from '@wexample/symfony-loader/js/Services/AdaptiveService';
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';

// A form of the application handed to a vue (`form_definition()`, symfony-forms'
// FormDefinitionBuilder) as the json-schema norm describes one (`web/form.schema.json`,
// copied in `schemas/web/`): its fields in order, each of a kind, where it is
// sent. Sent as the drawn form would be — to the form's processor, which
// validates, saves and answers (`form_submit_response.schema.json`). What
// `inline-edit` and `dynamic-form` share.

// What `form_definition(form, { id_placeholder })` puts where the entity's id
// stands in the address.
export const FORM_DEFINITION_ID_PLACEHOLDER = '__id__';

export type FormDefinitionChoice = { label: string; value: string | null; options?: FormDefinitionChoice[] };

export type FormDefinitionField = {
  type: string;
  name: string;
  label?: string;
  placeholder?: string | null;
  required?: boolean | null;
  minLength?: number;
  maxLength?: number;
  pattern?: string;
  value?: string | null;
  rows?: number;
  // What is attached to the field whatever its kind, by name (`field_base.schema.json`).
  extensions?: Record<string, Record<string, unknown>>;
  choices?: FormDefinitionChoice[];
  multiple?: boolean;
  // A key-value field's value: its pairs, in order (`key_value_input.schema.json`).
  pairs?: FormDefinitionPair[];
  // A tags field's value, its words, and how it offers and draws them
  // (`tags_input.schema.json`).
  tags?: string[];
  separator?: string;
  suggestions?: { value: string; label: string }[];
  suggestionsUrl?: string;
  tone?: string;
  glyph?: string;
  // A searched select's own options, its source and the label of its value
  // (`search_select_input.schema.json`).
  options?: { value: string; label: string }[];
  optionsUrl?: string;
  valueLabel?: string;
  // Yes or no: whether it is on (`switch_input.schema.json`, `checkbox_input.schema.json`).
  checked?: boolean;
  // A number's bounds and step (`number_input.schema.json`).
  min?: number;
  max?: number;
  step?: number | 'any';
  stepper?: boolean;
  // A colour's offered colours (`color_input.schema.json`).
  swatches?: { value: string; label: string }[];
  // A collection: the field each item is, each item's value, what one added
  // starts from (`collection_input.schema.json`).
  entry?: FormDefinitionField;
  items?: unknown[];
  template?: unknown;
  allowAdd?: boolean;
  allowDelete?: boolean;
};

export type FormDefinitionPair = { key: string; value: string };

export const FORM_DEFINITION_KIND_KEY_VALUE = 'key_value';

export const FORM_DEFINITION_KIND_TAGS = 'tags';

export const FORM_DEFINITION_KIND_COLLECTION = 'collection';

// The kinds saying yes or no: held as a boolean, posted as a drawn checkbox is.
export const FORM_DEFINITION_KINDS_TOGGLE = ['switch', 'checkbox'];

// The kinds whose value is not a text: held as the form holds them
// (`formDefinitionStartValue`), not as the definition's `value`.
export const FORM_DEFINITION_KINDS_HELD = [FORM_DEFINITION_KIND_KEY_VALUE, FORM_DEFINITION_KIND_TAGS, FORM_DEFINITION_KIND_COLLECTION, ...FORM_DEFINITION_KINDS_TOGGLE];

// What a yes or no given in `values` says: a boolean, or the words a record
// or a query holds it as.
export function formDefinitionChecked(raw: unknown): boolean {
  if (typeof raw === 'string') {
    return !['', '0', 'false', 'off', 'no'].includes(raw.trim().toLowerCase());
  }

  return Boolean(raw);
}

export type FormDefinition = {
  name: string;
  action: string;
  method?: string;
  token: { name: string; value: string } | null;
  fields: FormDefinitionField[];
};

// What the processor refused: each field's errors by the field's own name,
// the form's own beside them.
export type FormDefinitionErrors = {
  fields: Record<string, string[]>;
  form: string[];
};

export type FormDefinitionAnswer = {
  ok: boolean;
  payload: any;
  errors: FormDefinitionErrors;
};

export function formDefinitionField(form: FormDefinition, name: string): FormDefinitionField | null {
  return form.fields?.find((field) => field.name === name) ?? null;
}

// A field's name as it is sent, under the form's: `form[field]`.
export function formDefinitionFullName(form: FormDefinition, name: string): string {
  return form.name ? `${form.name}[${name}]` : name;
}

// A key-value field's pairs, given as its pairs or as a record
// `{ lang: 'fr' }`: the pairs, each a key and its text.
export function formDefinitionPairs(value: unknown): FormDefinitionPair[] {
  if (Array.isArray(value)) {
    return value.map((pair) => ({ key: String(pair?.key ?? ''), value: String(pair?.value ?? '') }));
  }

  if (value && typeof value === 'object') {
    return Object.entries(value as Record<string, unknown>).map(([key, text]) => ({ key, value: String(text ?? '') }));
  }

  return [];
}

// A field's value as the form holds it: the one given, or the field's own —
// pairs, words, a yes or no, a collection's items each as its entry holds
// one, a text.
export function formDefinitionStartValue(field: FormDefinitionField, given: unknown = undefined): unknown {
  if (field.type === FORM_DEFINITION_KIND_KEY_VALUE) {
    return formDefinitionPairs(given !== undefined ? given : field.pairs);
  }

  if (field.type === FORM_DEFINITION_KIND_TAGS) {
    const tags = given !== undefined ? given : field.tags;

    return Array.isArray(tags) ? [...tags] : tagsSplit(String(tags ?? ''), field.separator ?? ',');
  }

  if (FORM_DEFINITION_KINDS_TOGGLE.includes(field.type)) {
    return formDefinitionChecked(given !== undefined ? given : field.checked);
  }

  if (field.type === FORM_DEFINITION_KIND_COLLECTION) {
    const items = given !== undefined ? given : field.items;

    return (Array.isArray(items) ? items : []).map((item) => (field.entry ? formDefinitionStartValue(field.entry, item) : String(item ?? '')));
  }

  return String((given !== undefined ? given : field.value) ?? '');
}

// What an item added to a collection starts from: its example, or its
// entry's empty value.
export function formDefinitionCollectionTemplate(field: FormDefinitionField): unknown {
  if (field.template !== undefined && field.template !== null) {
    return field.entry ? formDefinitionStartValue(field.entry, field.template) : field.template;
  }

  return field.entry ? formDefinitionStartValue({ ...field.entry, value: null, checked: false, pairs: [], tags: [], items: [] }) : '';
}

// A field's value put in the data under its full name, as the drawn field
// posts it: pairs and items each under its index, words joined, a yes only
// while it is on, an expression as the text it is.
export function formDefinitionAppend(data: FormData, name: string, field: FormDefinitionField, value: unknown): void {
  if (field.type === FORM_DEFINITION_KIND_KEY_VALUE) {
    const pairs = formDefinitionPairs(value);

    pairs.forEach((pair, index) => {
      data.append(`${name}[${index}][key]`, pair.key);
      data.append(`${name}[${index}][value]`, pair.value);
    });

    // None: sent empty, so the pairs it held are taken off.
    if (!pairs.length) {
      data.append(name, '');
    }

    return;
  }

  if (field.type === FORM_DEFINITION_KIND_COLLECTION) {
    const items = Array.isArray(value) ? value : [];

    // None: nothing sent, which the form reads as a list emptied — a text
    // under the name would be refused, the collection taking a list.
    items.forEach((item, index) => formDefinitionAppend(data, `${name}[${index}]`, field.entry ?? { type: 'text', name: field.name }, item));

    return;
  }

  if (field.type === FORM_DEFINITION_KIND_TAGS) {
    const list = Array.isArray(value) ? value : String(value ?? '').split(field.separator ?? ',');

    data.append(name, tagsJoin(tagsUnique(list), field.separator ?? ','));

    return;
  }

  // An expression in the value's place (`extensions.expression`): sent as
  // the text it is, whatever the kind would have posted.
  if (fieldExpressionMatches(field.extensions?.expression?.pattern as string, value)) {
    data.append(name, String(value));

    return;
  }

  // Its value while it is on, nothing while it is off: what a drawn
  // checkbox posts, which the form reads back as off.
  if (FORM_DEFINITION_KINDS_TOGGLE.includes(field.type)) {
    if (formDefinitionChecked(value)) {
      data.append(name, String(field.value ?? '1'));
    }

    return;
  }

  if (value !== null && value !== undefined && typeof value !== 'object') {
    data.append(name, String(value));
  }
}

export function formDefinitionAction(form: FormDefinition, id: string | null = null): string {
  return id ? form.action.replace(FORM_DEFINITION_ID_PLACEHOLDER, id) : form.action;
}

// Every field with its value — the one given in `values`, by the field's
// name, or the form's own —, and the token.
export function formDefinitionData(form: FormDefinition, values: Record<string, unknown> = {}): FormData {
  const data = new FormData();

  // Each field with the value given, or its own: a text as it stands —
  // none, nothing sent —, any other as the form holds it.
  (form.fields ?? []).forEach((field) => {
    const own = FORM_DEFINITION_KINDS_HELD.includes(field.type) ? formDefinitionStartValue(field) : field.value;

    formDefinitionAppend(data, formDefinitionFullName(form, field.name), field, field.name in values ? values[field.name] : own);
  });

  if (form.token) {
    data.append(formDefinitionFullName(form, form.token.name), form.token.value);
  }

  return data;
}

// Sent, the answer read: what the processor refused translated as a drawn
// form's errors are, or that it could not be sent; what it said, told.
export async function submitFormDefinition(
  app: any,
  form: FormDefinition,
  { id = null, values = {}, failedMessage }: { id?: string | null; values?: Record<string, unknown>; failedMessage: string }
): Promise<FormDefinitionAnswer> {
  const payload = await app.getServiceOrFail(AdaptiveService).requestData(formDefinitionAction(form, id), {
    method: form.method ?? 'POST',
    body: formDefinitionData(form, values)
  });

  if (!payload || payload.ok === false) {
    return { ok: false, payload, errors: readFormDefinitionErrors(app, form, payload, failedMessage) };
  }

  notifyFormDefinition(app, payload);

  return { ok: true, payload, errors: { fields: {}, form: [] } };
}

// The answer's errors (`form.errors`, the fields under their full names)
// read back by the fields' own names, in the page's words.
export function readFormDefinitionErrors(app: any, form: FormDefinition, payload: any, failedMessage: string): FormDefinitionErrors {
  const locale = app.getServiceOrFail(LocaleService);
  const translate = (messages: string[] = []) => messages.map((message) => locale.trans(message, {}, payload?.translations ?? {}));
  const errors = payload?.form?.errors;
  const fields: Record<string, string[]> = {};

  (form.fields ?? []).forEach((field) => {
    const messages = errors?.fields?.[formDefinitionFullName(form, field.name)];

    if (messages?.length) {
      fields[field.name] = translate(messages);
    }
  });

  const formErrors = translate(errors?.form ?? []);

  // Refused with nothing said, or never answered: that it was not saved.
  if (!formErrors.length && !Object.keys(fields).length) {
    formErrors.push(failedMessage);
  }

  return { fields, form: formErrors };
}

export function notifyFormDefinition(app: any, payload: any): void {
  const notification = payload?.notification;

  if (notification) {
    app.services.toast?.show({
      type: notification.type,
      message: app.getServiceOrFail(LocaleService).trans(notification.message, {}, payload.translations ?? {})
    });
  }
}
