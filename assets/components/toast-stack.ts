import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private itemsEl?: HTMLElement;
  private onToastShowProxy?: EventListener;
  private onToastDismissProxy?: EventListener;
  private onToastClearProxy?: EventListener;

  attachHtmlElements() {
    super.attachHtmlElements();
    this.itemsEl = this.el.querySelector('.toast-stack--items') as HTMLElement;
    this.applyPosition();
  }

  protected async activateListeners(): Promise<void> {
    this.onToastShowProxy = this.onToastShow.bind(this);
    this.onToastDismissProxy = this.onToastDismiss.bind(this);
    this.onToastClearProxy = this.onToastClear.bind(this);

    document.addEventListener('toast:show', this.onToastShowProxy);
    document.addEventListener('toast:dismiss', this.onToastDismissProxy);
    document.addEventListener('toast:clear', this.onToastClearProxy);
  }

  protected async deactivateListeners(): Promise<void> {
    if (this.onToastShowProxy) {
      document.removeEventListener('toast:show', this.onToastShowProxy);
    }
    if (this.onToastDismissProxy) {
      document.removeEventListener('toast:dismiss', this.onToastDismissProxy);
    }
    if (this.onToastClearProxy) {
      document.removeEventListener('toast:clear', this.onToastClearProxy);
    }
  }

  private onToastShow(event: Event) {
    const detail = (event as CustomEvent).detail || {};
    const stackId = detail.stackId || 'default';
    if (this.options?.stackId && this.options.stackId !== stackId) {
      return;
    }
    const id = detail.id || `toast-${Date.now()}`;
    const type = detail.type || 'default';
    const title = detail.title;
    const message = detail.message || '';
    const allowHtml = detail.allowHtml === true;
    const timeout = detail.timeout ?? 4000;
    const sticky = detail.sticky === true;
    const maxToasts = detail.maxToasts ?? 6;
    const position = detail.position;

    const mountTarget = this.itemsEl;
    if (!mountTarget) {
      return;
    }

    if (position) {
      this.applyPosition(position);
    }

    const created = this.app.services.components.createComponentFromTemplate(
      this.options.toastTemplateName,
      {
        id,
        type,
        title,
        message,
        allowHtml,
        timeout,
        sticky
      },
      this,
      mountTarget
    );

    if (!created) {
      return;
    }

    Promise.resolve(created).then((result) => {
      if (!result) {
        return;
      }
      const toastEl = result.el;
      const toastBody = (toastEl.querySelector('.toast') as HTMLElement) || toastEl;
      toastBody.classList.add('toast-stack--item');
      toastBody.classList.add(`toast--${type}`);
      toastBody.setAttribute('data-toast-id', id);
      this.appendToast(toastBody, maxToasts);
    });
  }

  private applyPosition(forcedPosition?: string) {
    const position = forcedPosition || this.options?.position || 'br';
    const top = position.startsWith('t');
    const right = position.endsWith('r');

    this.el.classList.toggle('toast-stack--top', top);
    this.el.classList.toggle('toast-stack--bottom', !top);
    this.el.classList.toggle('toast-stack--right', right);
    this.el.classList.toggle('toast-stack--left', !right);
  }

  private onToastDismiss(event: Event) {
    const detail = (event as CustomEvent).detail || {};
    if (detail.id) {
      this.removeToast(detail.id);
    }
  }

  private onToastClear() {
    if (!this.itemsEl) {
      return;
    }

    this.itemsEl.innerHTML = '';
  }


  private appendToast(toastEl: HTMLElement, maxToasts: number) {
    if (!this.itemsEl) {
      return;
    }

    const children = Array.from(this.itemsEl.children);
    while (children.length > maxToasts) {
      const first = this.itemsEl.firstElementChild;
      if (!first) {
        break;
      }
      first.remove();
      children.shift();
    }
  }

  private removeToast(id: string) {
    if (!this.itemsEl) {
      return;
    }

    const toastEl = this.itemsEl.querySelector(`[data-toast-id="${id}"]`);
    if (toastEl) {
      toastEl.remove();
    }
  }

}
