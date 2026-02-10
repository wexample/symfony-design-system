import Component from '@wexample/symfony-loader/js/Class/Component';

type ConfirmAction = {
  key: string;
  value: string;
  label: string;
  role?: 'primary' | 'secondary' | 'destructive';
};

export default class extends Component {
  private actionsEl?: HTMLElement;

  protected async mounted(): Promise<void> {
    if (this.options?.variant) {
      this.el.classList.add(`confirm--${this.options.variant}`);
    }

    const titleEl = this.el.querySelector('[data-confirm-title]') as HTMLElement | null;
    const messageEl = this.el.querySelector('[data-confirm-message]') as HTMLElement | null;
    this.actionsEl = this.el.querySelector('[data-confirm-actions]') as HTMLElement | null;

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

    this.renderActions();

    await super.mounted();
  }

  private renderActions() {
    if (!this.actionsEl) {
      return;
    }

    this.actionsEl.innerHTML = '';

    const actions: ConfirmAction[] = this.options?.actions || [];
    actions.forEach((action) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `confirm--action confirm--action--${action.role || 'secondary'}`;
      button.textContent = action.label;
      button.dataset.confirmValue = action.value;
      button.dataset.confirmKey = action.key;
      button.addEventListener('click', () => this.resolve(action.value));
      this.actionsEl?.appendChild(button);
    });
  }

  private resolve(value: string) {
    if (this.options?.onResolve) {
      this.options.onResolve(value);
    }
  }
}
