import { tagsJoin, tagsUnique } from './TagsHelper';
import AdaptiveService from '@wexample/symfony-loader/js/Services/AdaptiveService';
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';

// A form of the application handed to a vue (`form_definition()`, symfony-forms'
// FormDefinitionBuilder) as the json-schema norm describes one (`web/form.json`,
// copied in `schemas/web/`): its fields in order, each of a kind, where it is
// sent. Sent as the drawn form would be — to the form's processor, which
// validates, saves and answers (`form_submit_response.json`). What
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
  choices?: FormDefinitionChoice[];
  multiple?: boolean;
  // A key-value field's value: its pairs, in order (`key_value_input.json`).
  pairs?: FormDefinitionPair[];
  // A tags field's value, its words, and how it offers and draws them
  // (`tags_input.json`).
  tags?: string[];
  separator?: string;
  suggestions?: { value: string; label: string }[];
  suggestionsUrl?: string;
  tone?: string;
  glyph?: string;
};

export type FormDefinitionPair = { key: string; value: string };

export const FORM_DEFINITION_KIND_KEY_VALUE = 'key_value';

export const FORM_DEFINITION_KIND_TAGS = 'tags';

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
