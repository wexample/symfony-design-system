import Component from '@wexample/symfony-loader/js/Class/Component';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import { confirmAskFor } from '../../js/Helper/ConfirmHelper';
import { rippleAgentPress } from '../../js/Helper/RippleHelper';

// Flips the switch and says so: `switch:change`, with the new state, bubbles
// from the switch for whatever the page hangs on it. A switch carrying a
// confirmation asks it before switching on, and stays off when refused.
//
// An agent turns it the same way a click does, confirmation included.
export default class extends Component {
  protected checked: boolean = false;
  private asking: boolean = false;

  private onToggle = async () => {
    await this.switchTo(!this.checked);
  };

  // Whether the switch ended in that state: false when a confirmation was
  // refused, or another one is already being asked.
  protected async switchTo(next: boolean): Promise<boolean> {
    if (next === this.checked) {
      return true;
    }

    if (this.asking) {
      return false;
    }

    if (next && this.el.dataset.confirmMessage) {
      this.asking = true;

      try {
        if (!(await this.askConfirmation())) {
          return false;
        }
      } finally {
        this.asking = false;
      }
    }

    this.setChecked(next);
    await this.changed(next);
    this.logAgentEvent(`Switched "${this.getAgentLabel() ?? 'switch'}" ${next ? 'on' : 'off'}`, { key: 'switched', detail: { checked: next } });

    return true;
  }

  // What switching does, once switched.
  protected async changed(checked: boolean): Promise<void> {
    this.el.dispatchEvent(new CustomEvent('switch:change', {
      bubbles: true,
      detail: { checked },
    }));
  }

  protected setChecked(checked: boolean): void {
    this.checked = checked;
    this.el.setAttribute('data-checked', String(checked));
    this.el.querySelector('.switch--pill')?.setAttribute('aria-checked', String(checked));
  }

  // A switch that should ask is never turned on without asking.
  private askConfirmation(): Promise<boolean> {
    return confirmAskFor(
      this.app,
      this.el,
      this.trans('WexampleSymfonyDesignSystemBundle.common.system::frontend.switch.cancel')
    );
  }

  // What the actions of `switch.ai.yml` do: the same as a click.
  public getAgentActions(): AgentActionsImplementation {
    const outcome = (switched: boolean) => ({ checked: this.checked, switched });
    // Pressed where the reader sees it, then switched.
    const press = () => rippleAgentPress(this.app, this.el.querySelector<HTMLElement>('.switch--pill'));

    return {
      get: () => ({ checked: this.checked }),
      set: async ({ checked }) => {
        await press();
        return outcome(await this.switchTo(checked === true));
      },
      toggle: async () => {
        await press();
        return outcome(await this.switchTo(!this.checked));
      },
    };
  }

  public getAgentState(): Record<string, any> {
    return { checked: this.checked };
  }

  // Named as the reader sees it: its label, or the name a switch showing none
  // carries for assistive technologies.
  public getAgentLabel(): string | undefined {
    return this.el.querySelector('.switch--label')?.textContent?.trim()
      || this.el.querySelector('.switch--pill')?.getAttribute('aria-label')
      || undefined;
  }

  protected async activateListeners(): Promise<void> {
    this.checked = this.el.dataset.checked === 'true';
    this.el.addEventListener('click', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onToggle);
  }
}
