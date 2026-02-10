import Component from '@wexample/symfony-loader/js/Class/Component';

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
    if (this.options?.variant) {
      this.el.classList.add(`confirm--${this.options.variant}`);
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
