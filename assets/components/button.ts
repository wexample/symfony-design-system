import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  async activateListeners(): Promise<void> {
    await super.activateListeners();

    const button = this.el as HTMLButtonElement;
    if (button.type === 'submit' && button.form) {
      button.form.addEventListener('submit', () => this.setLoading(true));
    }
  }

  setLoading(loading: boolean): void {
    const button = this.el as HTMLButtonElement;
    button.classList.toggle('is-loading', loading);
    button.disabled = loading;
    button.setAttribute('aria-busy', loading ? 'true' : 'false');
  }
}
