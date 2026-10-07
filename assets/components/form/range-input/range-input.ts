import Field from '../../../js/Class/Field';
import { numberFormat, numberStepDigits } from '../../../js/Helper/NumberHelper';

export default class extends Field {
  private inputEl?: HTMLInputElement;
  private figureEl?: HTMLElement;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.inputEl = this.el.querySelector('.form--range') as HTMLInputElement;
    this.figureEl = this.el.querySelector('.range-input--figure') as HTMLElement;

    this.inputEl?.addEventListener('input', this.onInput);
    this.onInput();
  }

  protected async deactivateListeners(): Promise<void> {
    this.inputEl?.removeEventListener('input', this.onInput);
    await super.deactivateListeners();
  }

  // The value said as it moves, in the page's locale and with the decimals
  // its step asks for — on the screen and to assistive technologies alike.
  private onInput = (): void => {
    if (!this.inputEl) {
      return;
    }

    const text = numberFormat(this.inputEl.value, numberStepDigits(this.inputEl.step));
    const suffix = this.inputEl.dataset.suffix;

    if (this.figureEl) {
      this.figureEl.textContent = text;
    }

    this.inputEl.setAttribute('aria-valuetext', suffix ? `${text} ${suffix}` : text);
  };
}
