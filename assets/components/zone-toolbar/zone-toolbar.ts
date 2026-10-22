import Component from '@wexample/symfony-loader/js/Class/Component';

// A bar of tools on end (`zone--toolbar`), as an IDE has one: each icon opens
// or closes a zone of the row — `data-zone-toggle`, the zone's id —, and of the
// icons of one group (`zone--toolbar-group`) one only is open at a time: opening
// one closes the other. Pressing the open one closes it. An icon naming a
// `data-ui-state` key has its state kept there, the page drawing it as it was
// left; the page draws the first state itself (`aria-pressed`, `zone--closed`).
export default class extends Component {
  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.el.addEventListener('click', this.onClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onClick);

    await super.deactivateListeners();
  }

  private onClick = (event: MouseEvent): void => {
    const button = (event.target as HTMLElement).closest<HTMLElement>('[data-zone-toggle]');

    if (!button || !this.el.contains(button)) {
      return;
    }

    event.preventDefault();

    const open = button.getAttribute('aria-pressed') !== 'true';
    const states: Record<string, boolean> = {};

    if (open) {
      const group = button.closest('.zone--toolbar-group') ?? this.el;

      group.querySelectorAll<HTMLElement>('[data-zone-toggle][aria-pressed="true"]').forEach((other) => {
        if (other !== button) {
          this.toggle(other, false, states);
        }
      });
    }

    this.toggle(button, open, states);

    if (Object.keys(states).length) {
      this.app.persistUiStates(states);
    }
  };

  private toggle(button: HTMLElement, open: boolean, states: Record<string, boolean>): void {
    button.setAttribute('aria-pressed', String(open));
    document.getElementById(button.dataset.zoneToggle ?? '')?.classList.toggle('zone--closed', !open);

    if (button.dataset.uiState) {
      states[button.dataset.uiState] = open;
    }
  }
}
