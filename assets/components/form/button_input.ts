import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private formEl: HTMLFormElement | null = null;
  private isClicked = false;

  protected async activateListeners(): Promise<void> {
    this.formEl = this.el.closest('form');
    this.el.addEventListener('click', this.onButtonClick);
    this.formEl?.addEventListener('submit', this.onFormSubmit);
    this.formEl?.addEventListener('loading:end', this.onFormLoadingEnd);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onButtonClick);
    this.formEl?.removeEventListener('submit', this.onFormSubmit);
    this.formEl?.removeEventListener('loading:end', this.onFormLoadingEnd);
  }

  private onButtonClick = (): void => {
    this.isClicked = true;
  };

  private onFormSubmit = (): void => {
    if (this.isClicked) {
      this.el.classList.add('is-loading');
    }
    this.isClicked = false;
  };

  private onFormLoadingEnd = (): void => {
    this.el.classList.remove('is-loading');
  };
}
