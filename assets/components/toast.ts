import Component from '@wexample/symfony-loader/js/Class/Component';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import AutoCloseMixin from '@wexample/symfony-loader/js/Class/Mixins/AutoCloseMixin';
import ActionLinksMixin from '@wexample/symfony-loader/js/Class/Mixins/ActionLinksMixin';

export default class extends Component {
  protected fadeOpen?: () => void;
  protected closeWithAnimation?: () => Promise<void>;

  async init() {
    FadeAnimationMixin.apply(this);
    AutoCloseMixin.apply(this);
    ActionLinksMixin.apply(this);
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
      const message = this.options?.message || '';
      const actions = this.options?.actions as Record<string, () => void> | undefined;
      const buildActionLinksHtml = (this as any).buildActionLinksHtml as
        | ((value: string) => string)
        | undefined;
      const bindActionLinks = (this as any).bindActionLinks as
        | ((rootEl: HTMLElement, actions: Record<string, () => void>) => void)
        | undefined;
      if (actions && Object.keys(actions).length && buildActionLinksHtml && bindActionLinks) {
        messageEl.innerHTML = buildActionLinksHtml(message);
        bindActionLinks(messageEl, actions);
      } else if (this.options?.allowHtml) {
        messageEl.innerHTML = message;
      } else {
        messageEl.textContent = message;
      }
    }

    if (!this.options?.sticky) {
      const timeout = this.options?.timeout ?? 4000;
      (this as any).startAutoClose(timeout, () => this.close());
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
    const unbindActionLinks = (this as any).unbindActionLinks as (() => void) | undefined;
    if (unbindActionLinks) {
      unbindActionLinks();
    }
    (this as any).clearAutoClose();
    await super.unmounted();
  }

  private onClickClose = () => {
    this.close();
  };

  private async close() {
    if (this.closeWithAnimation) {
      await this.closeWithAnimation();
      return;
    }
    await this.exit();
  }
}
