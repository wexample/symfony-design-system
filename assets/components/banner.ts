import Component from '@wexample/symfony-loader/js/Class/Component';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import ActionLinksMixin from '@wexample/symfony-loader/js/Class/Mixins/ActionLinksMixin';

export default class extends Component {
  protected fadeOpen?: () => void;
  protected closeWithAnimation?: () => Promise<void>;
  private onBannerShowProxy?: EventListener;
  private onBannerDismissProxy?: EventListener;
  private onBannerClearProxy?: EventListener;

  async init() {
    FadeAnimationMixin.apply(this);
    ActionLinksMixin.apply(this);
    await super.init();
  }

  protected async activateListeners(): Promise<void> {
    this.onBannerShowProxy = this.onBannerShow.bind(this);
    this.onBannerDismissProxy = this.onBannerDismiss.bind(this);
    this.onBannerClearProxy = this.onBannerClear.bind(this);

    document.addEventListener('banner:show', this.onBannerShowProxy);
    document.addEventListener('banner:dismiss', this.onBannerDismissProxy);
    document.addEventListener('banner:clear', this.onBannerClearProxy);
  }

  protected async deactivateListeners(): Promise<void> {
    if (this.onBannerShowProxy) {
      document.removeEventListener('banner:show', this.onBannerShowProxy);
    }
    if (this.onBannerDismissProxy) {
      document.removeEventListener('banner:dismiss', this.onBannerDismissProxy);
    }
    if (this.onBannerClearProxy) {
      document.removeEventListener('banner:clear', this.onBannerClearProxy);
    }
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

  private onBannerShow(event: Event) {
    const detail = (event as CustomEvent).detail || {};
    this.setBannerState(detail);
  }

  private onBannerDismiss(event: Event) {
    const detail = (event as CustomEvent).detail || {};
    if (detail.id && this.el.getAttribute('data-banner-id') !== detail.id) {
      return;
    }
    this.close();
  }

  private onBannerClear() {
    this.close();
  }

  private setBannerState(detail: any) {
    const type = detail.type || 'default';
    const title = detail.title;
    const message = detail.message || '';
    const allowHtml = detail.allowHtml === true;
    const actions = detail.actions;

    this.el.setAttribute('data-banner-id', detail.id || `banner-${Date.now()}`);
    this.el.classList.remove('banner--default', 'banner--info', 'banner--success', 'banner--warning', 'banner--error');
    this.el.classList.add(`banner--${type}`);

    const messageEl = this.el.querySelector('[data-banner-message]') as HTMLElement;
    if (messageEl) {
      const buildActionLinksHtml = (this as any).buildActionLinksHtml as
        | ((value: string) => string)
        | undefined;
      const bindActionLinks = (this as any).bindActionLinks as
        | ((rootEl: HTMLElement, actions: Record<string, () => void>) => void)
        | undefined;
      const unbindActionLinks = (this as any).unbindActionLinks as (() => void) | undefined;
      if (unbindActionLinks) {
        unbindActionLinks();
      }

      if (actions && Object.keys(actions).length && buildActionLinksHtml && bindActionLinks) {
        messageEl.innerHTML = buildActionLinksHtml(message);
        bindActionLinks(messageEl, actions);
      } else if (allowHtml) {
        messageEl.innerHTML = message;
      } else {
        messageEl.textContent = message;
      }
    }

    if (this.el.hasAttribute('hidden')) {
      this.el.removeAttribute('hidden');
    }
  }

  private async close() {
    if (this.closeWithAnimation) {
      await this.closeWithAnimation();
      return;
    }
    await this.exit();
  }
}
