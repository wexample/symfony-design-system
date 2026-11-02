import Component from '@wexample/symfony-loader/js/Class/Component';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import { rippleAgentPress } from '../../js/Helper/RippleHelper';

// Takes the option pressed and says so: `choice-list:change`, with its value,
// bubbles from the list for whatever the page hangs on it. The field above
// the options, when there is one, narrows them by their words.
//
// An agent chooses the same way a press does.
export default class extends Component {
  private onClick = (event: Event) => {
    const choice = (event.target as HTMLElement).closest<HTMLButtonElement>('.choice-list--choice');

    if (choice && !choice.disabled && this.el.contains(choice)) {
      this.choose(choice);
    }
  };

  private onFilter = () => {
    const query = this.getFilterInput()?.value.trim().toLowerCase() ?? '';
    let shown = 0;

    this.getItems().forEach((item) => {
      const visible = !query || (item.textContent ?? '').toLowerCase().includes(query);

      item.hidden = !visible;
      shown += visible ? 1 : 0;
    });

    const empty = this.el.querySelector<HTMLElement>('.choice-list--empty');

    if (empty) {
      empty.hidden = shown > 0;
    }
  };

  private getItems(): HTMLElement[] {
    return [...this.el.querySelectorAll<HTMLElement>('.choice-list--item')];
  }

  private getChoices(): HTMLButtonElement[] {
    return [...this.el.querySelectorAll<HTMLButtonElement>('.choice-list--choice')];
  }

  private getFilterInput(): HTMLInputElement | null {
    return this.el.querySelector<HTMLInputElement>('.choice-list--filter-input');
  }

  private getValue(): string | null {
    return this.getChoices().find((choice) => choice.getAttribute('aria-checked') === 'true')?.dataset.value ?? null;
  }

  private labelOf(choice: HTMLElement): string {
    return choice.querySelector('.choice-list--label')?.textContent?.trim() ?? '';
  }

  // Found by its value, or by its name as the reader sees it.
  private findChoice(item: string): HTMLButtonElement | null {
    const wanted = item.trim().toLowerCase();

    return this.getChoices().find((choice) => choice.dataset.value === item)
      ?? this.getChoices().find((choice) => this.labelOf(choice).toLowerCase() === wanted)
      ?? null;
  }

  protected choose(choice: HTMLButtonElement): void {
    this.getChoices().forEach((other) => other.setAttribute('aria-checked', String(other === choice)));

    const value = choice.dataset.value ?? null;

    this.el.dispatchEvent(new CustomEvent('choice-list:change', {
      bubbles: true,
      detail: { value },
    }));
    this.logAgentEvent(`Chose "${this.labelOf(choice)}" in "${this.getAgentLabel() ?? 'a list of choices'}"`, { key: 'chosen', detail: { value } });
  }

  // What the actions of `choice-list.ai.yml` do: the same as a press.
  public getAgentActions(): AgentActionsImplementation {
    return {
      items: () => ({
        items: this.getChoices().map((choice) => ({
          value: choice.dataset.value,
          item: this.labelOf(choice),
          chosen: choice.getAttribute('aria-checked') === 'true',
          disabled: choice.disabled || undefined,
        })),
      }),
      select: async ({ item }) => {
        const choice = this.findChoice(String(item ?? ''));

        if (!choice) {
          return { error: `No option "${item}" in this list.` };
        }

        if (choice.disabled) {
          return { error: `The option "${item}" cannot be chosen.` };
        }

        await rippleAgentPress(this.app, choice);
        this.choose(choice);

        return { value: this.getValue() };
      },
    };
  }

  public getAgentState(): Record<string, any> {
    return { value: this.getValue() };
  }

  public getAgentLabel(): string | undefined {
    return this.el.querySelector('.choice-list--items')?.getAttribute('aria-label') || undefined;
  }

  protected async activateListeners(): Promise<void> {
    this.el.addEventListener('click', this.onClick);
    this.getFilterInput()?.addEventListener('input', this.onFilter);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onClick);
    this.getFilterInput()?.removeEventListener('input', this.onFilter);
  }
}
