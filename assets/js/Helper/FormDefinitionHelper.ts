import { tagsJoin, tagsUnique } from './TagsHelper';
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
};

export type FormDefinitionPair = { key: string; value: string };

export const FORM_DEFINITION_KIND_KEY_VALUE = 'key_value';

export const FORM_DEFINITION_KIND_TAGS = 'tags';

// The kinds saying yes or no: held as a boolean, posted as a drawn checkbox is.
export const FORM_DEFINITION_KINDS_TOGGLE = ['switch', 'checkbox'];

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

export function formDefinitionAction(form: FormDefinition, id: string | null = null): string {
  return id ? form.action.replace(FORM_DEFINITION_ID_PLACEHOLDER, id) : form.action;
}

// Every field with its value — the one given in `values`, by the field's
// name, or the form's own —, and the token.
export function formDefinitionData(form: FormDefinition, values: Record<string, unknown> = {}): FormData {
  const data = new FormData();

  (form.fields ?? []).forEach((field) => {
    if (field.type === FORM_DEFINITION_KIND_KEY_VALUE) {
      const name = formDefinitionFullName(form, field.name);
      const pairs = formDefinitionPairs(field.name in values ? values[field.name] : field.pairs);

      // Each pair under its index, as the drawn field posts it; none, the
      // field is sent empty so the pairs it held are taken off.
      pairs.forEach((pair, index) => {
        data.append(`${name}[${index}][key]`, pair.key);
        data.append(`${name}[${index}][value]`, pair.value);
      });

      if (!pairs.length) {
        data.append(name, '');
      }

      return;
    }

    // The words joined by the separator, as the drawn field posts them.
    if (field.type === FORM_DEFINITION_KIND_TAGS) {
      const tags = field.name in values ? values[field.name] : field.tags;
      const list = Array.isArray(tags) ? tags : String(tags ?? '').split(field.separator ?? ',');

      data.append(formDefinitionFullName(form, field.name), tagsJoin(tagsUnique(list), field.separator ?? ','));

      return;
    }

    const given = field.name in values ? values[field.name] : undefined;

    // An expression in the value's place (`extensions.expression`): sent as
    // the text it is, whatever the kind would have posted.
    if (fieldExpressionMatches(field.extensions?.expression?.pattern as string, given)) {
      data.append(formDefinitionFullName(form, field.name), String(given));

      return;
    }

    // Its value while it is on, nothing while it is off: what a drawn
    // checkbox posts, which the form reads back as off.
    if (FORM_DEFINITION_KINDS_TOGGLE.includes(field.type)) {
      if (formDefinitionChecked(field.name in values ? values[field.name] : field.checked)) {
        data.append(formDefinitionFullName(form, field.name), String(field.value ?? '1'));
      }

      return;
    }

    const value = field.name in values ? values[field.name] : field.value;

    if (value !== null && value !== undefined && typeof value !== 'object') {
      data.append(formDefinitionFullName(form, field.name), String(value));
    }
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
