import Component from '@wexample/symfony-loader/js/Class/Component';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';

export default class extends Component {
  private timeoutId?: number;

  async init() {
    FadeAnimationMixin.apply(this);
    await super.init();
  }

  protected async mounted(): Promise<void> {
    const titleEl = this.el.querySelector('[data-toast-title]') as HTMLElement;
    const messageEl = this.el.querySelector('[data-toast-message]') as HTMLElement;
    const closeEl = this.el.querySelector('[data-toast-close]') as HTMLElement | null;

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

    closeEl?.addEventListener('click', this.onClickClose);
    if (this.fadeOpen) {
      this.fadeOpen();
    }
    await super.mounted();
  }

  protected async unmounted(): Promise<void> {
    const closeEl = this.el.querySelector('[data-toast-close]') as HTMLElement | null;
    closeEl?.removeEventListener('click', this.onClickClose);
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
