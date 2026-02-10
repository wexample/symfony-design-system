import Component from '@wexample/symfony-loader/js/Class/Component';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import { applyOverlayDialogLifecycle } from '@wexample/symfony-loader/js/Utils/OverlayDialogHelper';

type ConfirmAction = {
  key: string;
  value: string;
  label: string;
  role?: 'primary' | 'secondary' | 'destructive';
};

export default class extends Component {
  attachHtmlElements() {
    super.attachHtmlElements();
    this.attachHtmlElementsMap({
      title: '[data-confirm-title]',
      message: '[data-confirm-message]',
      actions: '[data-confirm-actions]',
    });
  }

  protected async mounted(): Promise<void> {
    if (this.options?.variant !== 'toast') {
      OverlayMixin.apply(this);
      applyOverlayDialogLifecycle(this);
    }

    if (this.options?.variant === 'toast') {
      this.el.classList.add('confirm--toast');
      // Ensure the toast variant can receive pointer events within the toast stack.
      this.el.classList.add('toast-stack--item');
    } else {
      this.el.classList.add('confirm--center');
    }

    const titleEl = this.elements.title as HTMLElement | undefined;
    const messageEl = this.elements.message as HTMLElement | undefined;
    const actionsEl = this.elements.actions as HTMLElement | undefined;

    if (titleEl) {
      if (this.options?.title) {
        titleEl.textContent = this.options.title;
        titleEl.removeAttribute('hidden');
      } else {
        titleEl.setAttribute('hidden', 'hidden');
      }
    }

    if (messageEl) {
      if (this.options?.message) {
        messageEl.textContent = this.options.message;
        messageEl.removeAttribute('hidden');
      } else {
        messageEl.setAttribute('hidden', 'hidden');
      }
    }

    this.renderActions(actionsEl);

    await super.mounted();
  }

  private renderActions(actionsEl?: HTMLElement) {
    if (!actionsEl) {
      return;
    }

    actionsEl.innerHTML = '';

    const actions: ConfirmAction[] = this.options?.actions || [];
    actions.forEach((action) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `confirm--action confirm--action--${action.role || 'secondary'}`;
      button.textContent = action.label;
      button.dataset.confirmValue = action.value;
      button.dataset.confirmKey = action.key;
      button.addEventListener('click', () => this.resolve(action.value));
      actionsEl.appendChild(button);
    });
  }

  private resolve(value: string) {
    if (this.options?.onResolve) {
      this.options.onResolve(value);
    }
  }

}
