import Component from '@wexample/symfony-loader/js/Class/Component';
import ActionLinksMixin from '@wexample/symfony-loader/js/Class/Mixins/ActionLinksMixin';
import { collapseHeight, expandHeight } from '@wexample/js-helpers/Helper/Height';

export default class extends Component {
  async init() {
    ActionLinksMixin.apply(this);
    await super.init();
  }

  protected async mounted(): Promise<void> {
    const type = (this.options?.type as string | undefined) || 'default';
    if (type) {
      this.el.classList.add(`banner--${type}`);
    }
    if (this.options?.class) {
      this.el.classList.add(this.options.class as string);
    }
    if (this.options?.floating) {
      this.el.classList.add('banner--floating');
    }

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

      if (actions && Object.keys(actions).length) {
        messageEl.innerHTML = buildActionLinksHtml(message);
        bindActionLinks(messageEl, actions);
      } else if (this.options?.allowHtml) {
        messageEl.innerHTML = message;
      } else {
        messageEl.textContent = message;
      }
    }

    await super.mounted();
  }

  protected async unmounted(): Promise<void> {
    (this as any).unbindActionLinks();
    await super.unmounted();
  }
}
