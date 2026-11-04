import AdaptiveService from '@wexample/symfony-loader/js/Services/AdaptiveService';
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';

// A form of the application handed to a vue (`form_props()`, symfony-forms'
// FormPropsBuilder), sent as the drawn form would be — to the form's
// processor, which validates, saves and says so. What `inline-edit` and
// `props-form` share.

// What `form_props(form, { id_placeholder })` puts where the entity's id
// stands in the address.
export const FORM_PROPS_ID_PLACEHOLDER = '__id__';

export type FormPropsField = {
  name: string;
  value: unknown;
  required: boolean;
  maxlength: number | null;
  label?: string | null;
  type?: string;
};

export type FormProps = {
  name: string;
  action: string;
  method: string;
  token: { name: string; value: string } | null;
  fields: Record<string, FormPropsField>;
};

// What the processor refused: each field's errors by its key in `fields`,
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

export function formPropsAction(form: FormProps, id: string | null = null): string {
  return id ? form.action.replace(FORM_PROPS_ID_PLACEHOLDER, id) : form.action;
}

// Every field with its value — the one given in `values`, by its key, or the
// form's own —, and the token.
export function formPropsData(form: FormProps, values: Record<string, unknown> = {}): FormData {
  const data = new FormData();

  Object.entries(form.fields ?? {}).forEach(([key, field]) => {
    const value = key in values ? values[key] : field.value;

    if (value !== null && value !== undefined && typeof value !== 'object') {
      data.append(field.name, String(value));
    }
  });

  if (form.token) {
    data.append(form.token.name, form.token.value);
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

export function readFormPropsErrors(app: any, form: FormProps, payload: any, failedMessage: string): FormPropsErrors {
  const locale = app.getServiceOrFail(LocaleService);
  const translate = (messages: string[] = []) => messages.map((message) => locale.trans(message, {}, payload?.translations ?? {}));
  const errors = payload?.form?.errors;
  const fields: Record<string, string[]> = {};

  Object.entries(form.fields ?? {}).forEach(([key, field]) => {
    const messages = errors?.fields?.[field.name];

    if (messages?.length) {
      fields[key] = translate(messages);
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
