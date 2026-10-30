import AgentNavigation from '@wexample/symfony-loader/js/Class/AgentNavigation';
import Component from '@wexample/symfony-loader/js/Class/Component';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import { buttonSetLoading } from '../../js/Helper/ButtonHelper';
import { confirmAskFor } from '../../js/Helper/ConfirmHelper';
import { rippleAgentPress, rippleOnPress } from '../../js/Helper/RippleHelper';

// A button carrying a confirmation (`confirm`) asks it before anything
// happens, whoever presses it — the reader, or an agent (`button.ai.yml`).
// One sending a form leaves the asking to the form's own (ConfirmService),
// which reads the same attributes: asked here as well, the reader would
// answer twice.
export default class extends Component {
  private stopRipple: () => void = () => {};

  // Set for the length of a press already confirmed, which the click
  // listener lets through.
  private confirmed: boolean = false;

  async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.stopRipple = rippleOnPress(this.el);
    this.el.addEventListener('click', this.onClick, true);

    const button = this.el as HTMLButtonElement;
    if (button.type === 'submit' && button.form) {
      button.form.addEventListener('submit', this.onSubmit);
    }
  }

  async deactivateListeners(): Promise<void> {
    (this.el as HTMLButtonElement).form?.removeEventListener('submit', this.onSubmit);
    this.el.removeEventListener('click', this.onClick, true);
    this.stopRipple();

    await super.deactivateListeners();
  }

  // A press to confirm is held back, asked, and pressed again once accepted.
  private onClick = (event: MouseEvent): void => {
    if (this.confirmed) {
      return;
    }

    this.logAgentEvent(`Pressed "${this.getAgentLabel() ?? 'button'}"`, { key: 'pressed' });

    if (!this.el.dataset.confirmMessage || this.submits()) {
      return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    void this.press();
  };

  // Whether the press went through: false when the confirmation was refused.
  async press(): Promise<boolean> {
    if (!(await confirmAskFor(this.app, this.el))) {
      return false;
    }

    this.pressConfirmed();

    return true;
  }

  // Pressed as already confirmed: this button lets it through, and so does
  // the form it sends, told the way ConfirmService tells it.
  private pressConfirmed(): void {
    const button = this.el as HTMLButtonElement;
    this.confirmed = true;

    if (this.submits()) {
      button.form.dataset.confirmState = 'confirmed';
    }

    try {
      button.click();
    } finally {
      this.confirmed = false;

      if (this.submits()) {
        delete button.form.dataset.confirmState;
      }
    }
  }

  private submits(): boolean {
    const button = this.el as HTMLButtonElement;

    return button.type === 'submit' && Boolean(button.form);
  }

  // Decided once the event has gone all the way up: a submit that something
  // above takes over, to send it in the background, leaves the page where it
  // is, and a button waiting for a navigation that never comes would spin for
  // ever. Whoever took it over says when it is busy.
  private onSubmit = (event: SubmitEvent): void => {
    window.setTimeout(() => {
      if (!event.defaultPrevented) {
        this.setLoading(true);
      }
    });
  };

  setLoading(loading: boolean): void {
    buttonSetLoading(this.el as HTMLButtonElement, loading);
  }

  // Its text, or the name a button showing an icon alone carries.
  public getAgentLabel(): string | undefined {
    return this.el.getAttribute('aria-label')
      || this.el.querySelector(':scope > span:last-child')?.textContent?.trim()
      || this.el.getAttribute('title')
      || undefined;
  }

  public getAgentState(): Record<string, any> {
    const button = this.el as HTMLButtonElement;

    return {
      disabled: button.disabled,
      submits: this.submits(),
      confirms: Boolean(this.el.dataset.confirmMessage),
    };
  }

  public getAgentActions(): AgentActionsImplementation {
    return {
      click: async () => {
        const button = this.el as HTMLButtonElement;
        const clicked = this.getAgentLabel();

        if (button.disabled) {
          throw new Error(`"${clicked}" is disabled.`);
        }

        await rippleAgentPress(this.app, button);

        // Asked before leaving: a refused confirmation keeps the page.
        if (this.submits()) {
          if (!(await confirmAskFor(this.app, button))) {
            return { clicked, refused: true };
          }

          return new AgentNavigation(() => this.pressConfirmed(), { clicked });
        }

        return (await this.press()) ? { clicked } : { clicked, refused: true };
      },
    };
  }
}
