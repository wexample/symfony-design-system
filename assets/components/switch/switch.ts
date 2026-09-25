import Component from '@wexample/symfony-loader/js/Class/Component';
import type ConfirmService from '../../js/Services/ConfirmService';

// Flips the switch and says so: `switch:change`, with the new state, bubbles
// from the switch for whatever the page hangs on it. A switch carrying a
// confirmation asks it before switching on, and stays off when refused.
export default class extends Component {
  private checked: boolean = false;
  private asking: boolean = false;

  private onToggle = async () => {
    if (this.asking) {
      return;
    }

    const next = !this.checked;

    if (next && this.el.dataset.confirmMessage) {
      this.asking = true;

      try {
        if (!(await this.askConfirmation())) {
          return;
        }
      } finally {
        this.asking = false;
      }
    }

    this.setChecked(next);
    this.el.dispatchEvent(new CustomEvent('switch:change', {
      bubbles: true,
      detail: { checked: next },
    }));
  };

  private setChecked(checked: boolean): void {
    this.checked = checked;
    this.el.setAttribute('data-checked', String(checked));
    this.el.querySelector('.switch--pill')?.setAttribute('aria-checked', String(checked));
  }

  // The system's confirm where the app has one, the browser's otherwise: a
  // switch that should ask is never turned on without asking.
  private async askConfirmation(): Promise<boolean> {
    const { confirmTitle, confirmMessage, confirmAccept } = this.el.dataset;
    const confirmService = (this.app.services as Record<string, unknown>).confirm as ConfirmService | undefined;

    if (!confirmService) {
      return window.confirm(confirmMessage);
    }

    const result = await confirmService.confirm({
      title: confirmTitle || undefined,
      message: confirmMessage ?? '',
      preset: 'ok_cancel',
      actions: confirmAccept
        ? [
          { key: 'y', value: 'ok', label: confirmAccept, role: 'primary' },
          { key: 'n', value: 'cancel', label: this.trans('WexampleSymfonyDesignSystemBundle.common.system::frontend.switch.cancel'), role: 'secondary' },
        ]
        : undefined,
    });

    return result === 'ok';
  }

  protected async activateListeners(): Promise<void> {
    this.checked = this.el.dataset.checked === 'true';
    this.el.addEventListener('click', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onToggle);
  }
}
