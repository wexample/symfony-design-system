import Component from '@wexample/symfony-loader/js/Class/Component';

export default abstract class Field extends Component {
  private formEl: HTMLFormElement | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.formEl = this.el.closest('form');
    this.formEl?.addEventListener('submit', this.onFormSubmit);
    this.formEl?.addEventListener('loading:end', this.onFormLoadingEnd);
  }

  protected async deactivateListeners(): Promise<void> {
    await super.deactivateListeners();

    this.formEl?.removeEventListener('submit', this.onFormSubmit);
    this.formEl?.removeEventListener('loading:end', this.onFormLoadingEnd);
  }

  private onFormSubmit = (): void => {
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
}
