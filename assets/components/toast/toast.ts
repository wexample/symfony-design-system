import Component from '@wexample/symfony-loader/js/Class/Component';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import AutoCloseMixin from '@wexample/symfony-loader/js/Class/Mixins/AutoCloseMixin';
import ActionLinksMixin from '@wexample/symfony-loader/js/Class/Mixins/ActionLinksMixin';
import type { NotificationActionInterface } from '@wexample/symfony-loader/js/Interfaces/FormResponsePayloadInterface';
import { loadIntoTarget, targetEmbeddedOf } from '../../js/Helper/TargetHelper';

export default class extends Component {
  private actionEl?: HTMLAnchorElement;

  async init() {
    FadeAnimationMixin.apply(this);
    AutoCloseMixin.apply(this);
    ActionLinksMixin.apply(this);
    await super.init();
  }

  protected async mounted(): Promise<void> {
    if (this.options?.type) {
      this.el.classList.add(`toast--${this.options.type}`);
    }

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
    if (actions && Object.keys(actions).length) {
      messageEl.innerHTML = buildActionLinksHtml(message);
      bindActionLinks(messageEl, actions);
    } else if (this.options?.allowHtml) {
      messageEl.innerHTML = message;
    } else {
      messageEl.textContent = message;
    }
    }

    this.renderAction();

    if (!this.options?.sticky) {
      const timeout = this.options?.timeout ?? 4000;
      (this as any).startAutoClose(timeout, () => this.close());
    }

    closeEl?.addEventListener('click', this.onClickClose);
    await (this as FadeAnimationMixin).fadeOpen();
    await super.mounted();
  }

  protected async unmounted(): Promise<void> {
    const closeEl = this.el.querySelector('[data-toast-close]') as HTMLElement | null;
    closeEl?.removeEventListener('click', this.onClickClose);
    this.actionEl?.removeEventListener('click', this.onClickAction);
    (this as any).unbindActionLinks();
    (this as any).clearAutoClose();
    await super.unmounted();
  }

  // What to do next, as a button under the message: a link, or with a
  // `target` the page opened where a target button would — `panel`, `modal`.
  private renderAction(): void {
    const action = this.options?.action as NotificationActionInterface | undefined;
    const actionsEl = this.el.querySelector('[data-toast-actions]') as HTMLElement | null;

    if (!action || !actionsEl) {
      return;
    }

    const link = document.createElement('a');
    link.className = 'button button--flat';
    link.href = action.href;

    if (action.icon) {
      const iconEl = document.createElement('span');
      iconEl.className = 'button--icon';
      iconEl.innerHTML = (this.app.getServiceOrFail('icon') as any).icon(action.icon);
      link.append(iconEl);
    }

    const labelEl = document.createElement('span');
    labelEl.textContent = action.label;
    link.append(labelEl);

    link.addEventListener('click', this.onClickAction);
    actionsEl.append(link);
    actionsEl.removeAttribute('hidden');
    this.actionEl = link;
  }

  private onClickAction = (event: MouseEvent) => {
    const action = this.options?.action as NotificationActionInterface | undefined;

    // A modified click stays a navigation, as a target button keeps it.
    if (!action?.target || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
      return;
    }

    event.preventDefault();
    loadIntoTarget(this.app, action.target, action.href, {
      targetEmbedded: targetEmbeddedOf(this.actionEl ?? null),
    });
    this.close();
  };

  private onClickClose = () => {
    this.close();
  };

  private async close() {
    await (this as FadeAnimationMixin).closeWithAnimation();
  }
}
