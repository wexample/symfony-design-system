import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private formEl: HTMLFormElement | null = null;

  protected async activateListeners(): Promise<void> {
    this.formEl = this.el.closest('form');
    this.formEl?.addEventListener('submit', this.onFormSubmit);
  }

  protected async deactivateListeners(): Promise<void> {
    this.formEl?.removeEventListener('submit', this.onFormSubmit);
  }

  private onFormSubmit = (): void => {
    this.el.classList.add('is-loading');
  };
}
