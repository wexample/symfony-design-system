import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private timeoutId?: number;

  protected async mounted(): Promise<void> {
    const titleEl = this.el.querySelector('[data-toast-title]') as HTMLElement;
    const messageEl = this.el.querySelector('[data-toast-message]') as HTMLElement;

    if (titleEl) {
      if (this.options?.title) {
        titleEl.textContent = this.options.title;
        titleEl.removeAttribute('hidden');
      } else {
        titleEl.setAttribute('hidden', 'hidden');
      }
    }

    if (messageEl) {
      if (this.options?.allowHtml) {
        messageEl.innerHTML = this.options?.message || '';
      } else {
        messageEl.textContent = this.options?.message || '';
      }
    }

    if (!this.options?.sticky) {
      const timeout = this.options?.timeout ?? 4000;
      this.timeoutId = window.setTimeout(() => {
        this.close();
      }, timeout);
    }

    this.el.addEventListener('click', this.onClickClose);
    await super.mounted();
  }

  protected async unmounted(): Promise<void> {
    this.el.removeEventListener('click', this.onClickClose);
    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
    }
    await super.unmounted();
  }

  private onClickClose = () => {
    this.close();
  };

  private async close() {
    await this.exit();
  }
}
