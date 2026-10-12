import Field from '../../../js/Class/Field';
import { numberParse } from '../../../js/Helper/NumberHelper';

// A number field takes a step down or up from its buttons (`stepper`), and
// takes a figure pasted as the page's language writes it —
// « 1,5 » or « 1 234,50 » in French, « 1,234.5 » in English —, which the
// browser would otherwise drop or misread, a number control knowing the
// point alone.
export default class extends Field {
  private inputEl: HTMLInputElement | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.inputEl = this.el.querySelector('input[type="number"]');
    this.inputEl?.addEventListener('paste', this.onPaste);
    this.inputEl?.addEventListener('input', this.stepperUpdate);
    this.stepButton('down')?.addEventListener('click', this.onStepDown);
    this.stepButton('up')?.addEventListener('click', this.onStepUp);
    this.stepperUpdate();
  }

  protected async deactivateListeners(): Promise<void> {
    this.inputEl?.removeEventListener('paste', this.onPaste);
    this.inputEl?.removeEventListener('input', this.stepperUpdate);
    this.stepButton('down')?.removeEventListener('click', this.onStepDown);
    this.stepButton('up')?.removeEventListener('click', this.onStepUp);

    await super.deactivateListeners();
  }

  private stepButton(direction: 'down' | 'up'): HTMLButtonElement | null {
    return this.el.querySelector(`.form--stepper-${direction}`);
  }

  private onStepDown = (): void => this.step(-1);

  private onStepUp = (): void => this.step(1);

  // One step — the field's `step`, one where it has none or takes any —,
  // within its `min` and `max`, told as a change typed would be. Counted here
  // rather than by the browser's stepUp(), which refuses `step="any"`.
  private step(direction: number): void {
    const input = this.inputEl;

    if (!input) {
      return;
    }

    const step = Number(input.step) > 0 ? Number(input.step) : 1;
    const current = Number.isNaN(input.valueAsNumber) ? 0 : input.valueAsNumber;
    let value = current + direction * step;

    if (input.min !== '') {
      value = Math.max(value, Number(input.min));
    }

    if (input.max !== '') {
      value = Math.min(value, Number(input.max));
    }

    // No float dust: 0.1 + 0.2 is written 0.3.
    input.value = String(Number(value.toFixed(10)));
    this.notifyChanged(input);
  }

  // A bound reached, its button rests.
  private stepperUpdate = (): void => {
    const input = this.inputEl;

    if (!input) {
      return;
    }

    const value = input.valueAsNumber;
    const down = this.stepButton('down');
    const up = this.stepButton('up');

    if (down) {
      down.disabled = input.disabled || (input.min !== '' && !Number.isNaN(value) && value <= Number(input.min));
    }

    if (up) {
      up.disabled = input.disabled || (input.max !== '' && !Number.isNaN(value) && value >= Number(input.max));
    }
  };

  private onPaste = (event: ClipboardEvent): void => {
    const value = numberParse(event.clipboardData?.getData('text') ?? '');

    // Not a figure: the browser does as it does.
    if (value === null || !this.inputEl) {
      return;
    }

    event.preventDefault();
    this.inputEl.value = String(value);
    this.notifyChanged(this.inputEl);
  };
}
