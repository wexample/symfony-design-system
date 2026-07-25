import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private formEl: HTMLFormElement | null = null;

  protected async activateListeners(): Promise<void> {
    this.formEl = this.el.closest('form');
    this.formEl?.addEventListener('submit', this.onFormSubmit);
    this.formEl?.addEventListener('loading:end', this.onFormLoadingEnd);
  }

  protected async deactivateListeners(): Promise<void> {
    this.formEl?.removeEventListener('submit', this.onFormSubmit);
    this.formEl?.removeEventListener('loading:end', this.onFormLoadingEnd);
  }

  private onFormSubmit = (): void => {
    this.el.classList.add('is-loading');
  };

  private onFormLoadingEnd = (): void => {
    this.el.classList.remove('is-loading');
  };
}
