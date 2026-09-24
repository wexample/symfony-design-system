import Field from '../../../js/Class/Field';
import { otpInputCells, otpInputClean } from '../../../js/Helper/OtpInputHelper';

export default class extends Field {
  private inputEl: HTMLInputElement | null = null;
  private cellEls: HTMLElement[] = [];
  private wasComplete: boolean = false;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.inputEl = this.el.querySelector('.otp-input--field');
    this.cellEls = Array.from(this.el.querySelectorAll<HTMLElement>('.otp-input--cell'));
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.inputEl?.addEventListener('input', this.onInput);
    // Where the caret stands is the cell drawn as active, whichever way it got there.
    document.addEventListener('selectionchange', this.render);
    this.inputEl?.addEventListener('focus', this.render);
    this.inputEl?.addEventListener('blur', this.render);

    this.wasComplete = this.isComplete;
    this.render();
  }

  protected async deactivateListeners(): Promise<void> {
    await super.deactivateListeners();

    this.inputEl?.removeEventListener('input', this.onInput);
    document.removeEventListener('selectionchange', this.render);
    this.inputEl?.removeEventListener('focus', this.render);
    this.inputEl?.removeEventListener('blur', this.render);
  }

  private get length(): number {
    return Number(this.inputEl?.dataset.length) || 6;
  }

  private get isComplete(): boolean {
    return (this.inputEl?.value.length ?? 0) === this.length;
  }

  private onInput = (): void => {
    const input = this.inputEl;

    if (!input) {
      return;
    }

    const cleaned = otpInputClean(input.value, this.length, input.dataset.alphanumeric !== undefined);

    if (cleaned !== input.value) {
      input.value = cleaned;
    }

    this.render();

    // Only on the keystroke that completes it: a code corrected in place does
    // not send the form a second time, and an agent filling it hands the
    // submission back to whoever asked.
    const complete = this.isComplete;

    if (complete && !this.wasComplete && !this.isAssisted && input.dataset.autoSubmit !== undefined) {
      input.form?.requestSubmit();
    }

    this.wasComplete = complete;
  };

  private render = (): void => {
    const input = this.inputEl;

    if (!input) {
      return;
    }

    const focused = document.activeElement === input;

    otpInputCells(input.value, this.length, focused ? input.selectionStart : null)
      .forEach((cell, index) => {
        const el = this.cellEls[index];

        if (!el) {
          return;
        }

        el.textContent = cell.char;
        el.classList.toggle('is-filled', cell.char !== '');
        el.classList.toggle('is-active', cell.active);
      });
  };
}
