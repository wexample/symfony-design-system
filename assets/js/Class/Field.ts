import Component from '@wexample/symfony-loader/js/Class/Component';
import type { FieldControllerInterface } from '@wexample/js-api/Vue/FieldControllerInterface';

export default abstract class Field extends Component implements FieldControllerInterface {
  private formEl: HTMLFormElement | null = null;

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
