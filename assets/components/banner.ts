import Component from '@wexample/symfony-loader/js/Class/Component';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import ActionLinksMixin from '@wexample/symfony-loader/js/Class/Mixins/ActionLinksMixin';

export default class extends Component {
  protected fadeOpen?: () => void;
  protected closeWithAnimation?: () => Promise<void>;

  async init() {
    FadeAnimationMixin.apply(this);
    ActionLinksMixin.apply(this);
    await super.init();
  }

  protected async mounted(): Promise<void> {
    const messageEl = this.el.querySelector('[data-banner-message]') as HTMLElement;

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

    if (this.fadeOpen) {
      this.fadeOpen();
    }
    await super.mounted();
  }

  protected async unmounted(): Promise<void> {
    const unbindActionLinks = (this as any).unbindActionLinks as (() => void) | undefined;
    if (unbindActionLinks) {
      unbindActionLinks();
    }
    await super.unmounted();
  }
}
