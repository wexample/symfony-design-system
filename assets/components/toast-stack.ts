import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private itemsEl?: HTMLElement;
  private onToastShowProxy?: EventListener;
  private onToastDismissProxy?: EventListener;
  private onToastClearProxy?: EventListener;

  attachHtmlElements() {
    super.attachHtmlElements();
    this.itemsEl = this.el.querySelector('.toast-stack--items') as HTMLElement;
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
    const id = detail.id || `toast-${Date.now()}`;
    const type = detail.type || 'default';
    const title = detail.title;
    const message = detail.message || '';
    const allowHtml = detail.allowHtml === true;
    const timeout = detail.timeout ?? 4000;
    const sticky = detail.sticky === true;
    const maxToasts = detail.maxToasts ?? 6;

    const toastEl = this.buildToastElement(id, type, title, message, allowHtml);
    this.appendToast(toastEl, maxToasts);

    if (!sticky) {
      setTimeout(() => this.removeToast(id), timeout);
    }
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

  private buildToastElement(
    id: string,
    type: string,
    title: string | undefined,
    message: string,
    allowHtml: boolean
  ): HTMLElement {
    const toastEl = document.createElement('div');
    toastEl.className = `toast-stack--item toast--${type}`;
    toastEl.setAttribute('data-toast-id', id);

    if (title) {
      const titleEl = document.createElement('div');
      titleEl.className = 'toast--title';
      titleEl.textContent = title;
      toastEl.appendChild(titleEl);
    }

    const messageEl = document.createElement('div');
    messageEl.className = 'toast--message';
    if (allowHtml) {
      messageEl.innerHTML = message;
    } else {
      messageEl.textContent = message;
    }
    toastEl.appendChild(messageEl);

    return toastEl;
  }

  private appendToast(toastEl: HTMLElement, maxToasts: number) {
    if (!this.itemsEl) {
      return;
    }

    this.itemsEl.appendChild(toastEl);

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

  addToast(html: string) {
    if (!this.itemsEl) {
      return;
    }

    const item = document.createElement('div');
    item.className = 'toast-stack--item';
    item.innerHTML = html;
    this.itemsEl.appendChild(item);
  }
}
