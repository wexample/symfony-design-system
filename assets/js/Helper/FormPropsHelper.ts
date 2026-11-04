import AdaptiveService from '@wexample/symfony-loader/js/Services/AdaptiveService';
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';

// A form of the application handed to a vue (`form_props()`, symfony-forms'
// FormPropsBuilder) as the json-schema norm describes one (`web/form.json`,
// copied in `schemas/web/`): its fields in order, each of a kind, where it is
// sent. Sent as the drawn form would be — to the form's processor, which
// validates, saves and answers (`form_submit_response.json`). What
// `inline-edit` and `props-form` share.

// What `form_props(form, { id_placeholder })` puts where the entity's id
// stands in the address.
export const FORM_PROPS_ID_PLACEHOLDER = '__id__';

export type FormPropsChoice = { label: string; value: string | null; options?: FormPropsChoice[] };

export type FormPropsField = {
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
  choices?: FormPropsChoice[];
  multiple?: boolean;
};

export type FormProps = {
  name: string;
  action: string;
  method?: string;
  token: { name: string; value: string } | null;
  fields: FormPropsField[];
};

// What the processor refused: each field's errors by the field's own name,
// the form's own beside them.
export type FormPropsErrors = {
  fields: Record<string, string[]>;
  form: string[];
};

export type FormPropsAnswer = {
  ok: boolean;
  payload: any;
  errors: FormPropsErrors;
};

export function formPropsField(form: FormProps, name: string): FormPropsField | null {
  return form.fields?.find((field) => field.name === name) ?? null;
}

// A field's name as it is sent, under the form's: `form[field]`.
export function formPropsFullName(form: FormProps, name: string): string {
  return form.name ? `${form.name}[${name}]` : name;
}

export function formPropsAction(form: FormProps, id: string | null = null): string {
  return id ? form.action.replace(FORM_PROPS_ID_PLACEHOLDER, id) : form.action;
}

// Every field with its value — the one given in `values`, by the field's
// name, or the form's own —, and the token.
export function formPropsData(form: FormProps, values: Record<string, unknown> = {}): FormData {
  const data = new FormData();

  (form.fields ?? []).forEach((field) => {
    const value = field.name in values ? values[field.name] : field.value;

    if (value !== null && value !== undefined && typeof value !== 'object') {
      data.append(formPropsFullName(form, field.name), String(value));
    }
  });

  if (form.token) {
    data.append(formPropsFullName(form, form.token.name), form.token.value);
  }

  return data;
}

// Sent, the answer read: what the processor refused translated as a drawn
// form's errors are, or that it could not be sent; what it said, told.
export async function submitFormProps(
  app: any,
  form: FormProps,
  { id = null, values = {}, failedMessage }: { id?: string | null; values?: Record<string, unknown>; failedMessage: string }
): Promise<FormPropsAnswer> {
  const payload = await app.getServiceOrFail(AdaptiveService).requestData(formPropsAction(form, id), {
    method: form.method ?? 'POST',
    body: formPropsData(form, values)
  });

  if (!payload || payload.ok === false) {
    return { ok: false, payload, errors: readFormPropsErrors(app, form, payload, failedMessage) };
  }

  notifyFormProps(app, payload);

  return { ok: true, payload, errors: { fields: {}, form: [] } };
}

// The answer's errors (`form.errors`, the fields under their full names)
// read back by the fields' own names, in the page's words.
export function readFormPropsErrors(app: any, form: FormProps, payload: any, failedMessage: string): FormPropsErrors {
  const locale = app.getServiceOrFail(LocaleService);
  const translate = (messages: string[] = []) => messages.map((message) => locale.trans(message, {}, payload?.translations ?? {}));
  const errors = payload?.form?.errors;
  const fields: Record<string, string[]> = {};

  (form.fields ?? []).forEach((field) => {
    const messages = errors?.fields?.[formPropsFullName(form, field.name)];

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

export function notifyFormProps(app: any, payload: any): void {
  const notification = payload?.notification;

  if (notification) {
    app.services.toast?.show({
      type: notification.type,
      message: app.getServiceOrFail(LocaleService).trans(notification.message, {}, payload.translations ?? {})
    });
  }
}
