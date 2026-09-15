import Component from '@wexample/symfony-loader/js/Class/Component';
import type { FieldControllerInterface } from '@wexample/js-api/Vue/FieldControllerInterface';
import {
  assistanceWriteText,
  type AssistanceWriteOptions,
} from '@wexample/js-api/Helper/Assistance';

// Controls a person writes into character by character, and which therefore get
// their assisted value spelled out rather than dropped in.
const TEXT_INPUT_TYPES = ['text', 'email', 'url', 'search', 'tel', 'password'];

export default abstract class Field extends Component implements FieldControllerInterface {
  private formEl: HTMLFormElement | null = null;
  private assisted: boolean = false;
  private assistanceAbort: AbortController | null = null;

  get fieldName(): string {
    return (this.el.querySelector<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>('[name]'))?.name ?? '';
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.formEl = this.el.closest('form');
    // Listen to loading:start (not submit) so inputs are disabled
    // after the POST is already sent. Disabled fields are excluded from form
    // data — disabling on submit would empty the POST.
    this.formEl?.addEventListener('loading:start', this.onFormLoadingStart);
    this.formEl?.addEventListener('loading:end', this.onFormLoadingEnd);
  }

  protected async deactivateListeners(): Promise<void> {
    await super.deactivateListeners();

    this.formEl?.removeEventListener('loading:start', this.onFormLoadingStart);
    this.formEl?.removeEventListener('loading:end', this.onFormLoadingEnd);
  }

  private onFormLoadingStart = (): void => {
    // Whatever an agent was still writing lands at once: a form leaving with a
    // pulsing field behind it reads as something having gone wrong.
    this.assistanceDeactivate();
    this.disable();
  };

  private onFormLoadingEnd = (): void => {
    this.enable();
  };

  public disable(): void {
    this.el
      .querySelectorAll('input:not([type="submit"]), textarea, select, button:not([type="submit"])')
      .forEach((el) => {
        (el as HTMLInputElement).disabled = true;
      });
    this.el.classList.add('is-disabled');
  }

  public enable(): void {
    this.el
      .querySelectorAll('input:not([type="submit"]), textarea, select, button:not([type="submit"])')
      .forEach((el) => {
        (el as HTMLInputElement).disabled = false;
      });
    this.el.classList.remove('is-disabled');
  }

  public get isAssisted(): boolean {
    return this.assisted;
  }

  /**
   * Hands the field over. The whole group goes inert rather than each control
   * being locked one by one: what is being written is not to be argued with, and
   * a field made `disabled` would drop out of the submission along with the value
   * that was just written into it.
   */
  public assistanceActivate(): void {
    if (this.assisted) {
      return;
    }

    this.assisted = true;
    this.el.classList.add('is-assisted');
    this.el.inert = true;
  }

  public assistanceDeactivate(): void {
    if (!this.assisted) {
      return;
    }

    // Whatever was still being written lands on its value at once.
    this.assistanceAbort?.abort();
    this.assistanceAbort = null;
    this.assisted = false;
    this.el.classList.remove('is-assisted');
    this.el.inert = false;
  }

  public async setValueAssisted(
    value: unknown,
    options: AssistanceWriteOptions = {}
  ): Promise<void> {
    this.assistanceActivate();

    const controller = new AbortController();
    this.assistanceAbort = controller;

    try {
      await this.writeValueAssisted(value, { ...options, signal: controller.signal });
    } finally {
      this.assistanceDeactivate();
    }
  }

  /**
   * How this field spells a value out. The common controls are handled here;
   * a field that is not one of them — a custom select, a picker — says so by
   * overriding this and nothing else.
   */
  protected async writeValueAssisted(
    value: unknown,
    options: AssistanceWriteOptions
  ): Promise<void> {
    const control = this.assistedControl;

    if (!control) {
      return;
    }

    if (control instanceof HTMLInputElement
      && (control.type === 'checkbox' || control.type === 'radio')) {
      control.checked = Boolean(value);
      this.notifyChanged(control);

      return;
    }

    if (control instanceof HTMLSelectElement) {
      control.value = String(value ?? '');
      this.notifyChanged(control);

      return;
    }

    const text = String(value ?? '');

    if (control instanceof HTMLTextAreaElement
      || (control instanceof HTMLInputElement && TEXT_INPUT_TYPES.includes(control.type))) {
      await assistanceWriteText(
        (written) => {
          control.value = written;
          control.dispatchEvent(new Event('input', { bubbles: true }));
        },
        text,
        options
      );
      this.notifyChanged(control);

      return;
    }

    (control as HTMLInputElement).value = text;
    this.notifyChanged(control);
  }

  /**
   * The one control the field stands for. A field holding several — a radio
   * group — answers with none and writes its value its own way.
   */
  protected get assistedControl(): HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement | null {
    return this.el.querySelector('input:not([type="hidden"]), textarea, select');
  }

  protected notifyChanged(control: HTMLElement): void {
    control.dispatchEvent(new Event('input', { bubbles: true }));
    control.dispatchEvent(new Event('change', { bubbles: true }));
  }

  public setErrors(errors: string[]): void {
    this.clearErrors();
    if (!errors.length) {
      return;
    }

    const container = document.createElement('div');
    container.className = 'form--field-errors';
    const list = document.createElement('ul');
    errors.forEach((message) => {
      const item = document.createElement('li');
      item.textContent = message;
      list.appendChild(item);
    });
    container.appendChild(list);
    this.el.appendChild(container);
    this.el.classList.add('has-error');
  }

  public clearErrors(): void {
    this.el.querySelectorAll('.form--field-errors').forEach((el) => el.remove());
    this.el.classList.remove('has-error');
  }
}
