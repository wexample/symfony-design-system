import Field from '../../../js/Class/Field';
import {
  otpInputCells,
  otpInputClean,
  otpInputComplete,
  otpInputErase,
  otpInputFrom,
  otpInputMove,
  otpInputValue,
  otpInputWrite,
  type OtpInputState,
} from '../../../js/Helper/OtpInputHelper';

// How long a corrected code waits for another correction before it is sent.
const OTP_INPUT_CORRECTION_DELAY = 1000;

/**
 * The cells are what is written in, the input only what carries the code: a
 * click lands on the cell clicked, a key typed replaces what that cell held,
 * an erased digit leaves its cell empty. The browser keeps what it does best
 * on the input itself — a paste, the code the phone offers, replacing the
 * whole code since all of it stays selected.
 */
export default class extends Field {
  private inputEl: HTMLInputElement | null = null;
  private cellEls: HTMLElement[] = [];
  private state: OtpInputState = { chars: [], active: 0 };
  private submitTimer?: number;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.inputEl = this.el.querySelector('.otp-input--field');
    this.cellEls = Array.from(this.el.querySelectorAll<HTMLElement>('.otp-input--cell'));
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    const input = this.inputEl;

    if (!input) {
      return;
    }

    this.state = otpInputFrom(input.value, this.length);
    input.addEventListener('beforeinput', this.onBeforeInput);
    input.addEventListener('input', this.onInput);
    input.addEventListener('keydown', this.onKeyDown);
    input.addEventListener('focus', this.render);
    input.addEventListener('blur', this.render);
    this.el.querySelector('.otp-input--control')?.addEventListener('mousedown', this.onMouseDown);
    this.render();
  }

  protected async deactivateListeners(): Promise<void> {
    await super.deactivateListeners();

    const input = this.inputEl;
    input?.removeEventListener('beforeinput', this.onBeforeInput);
    input?.removeEventListener('input', this.onInput);
    input?.removeEventListener('keydown', this.onKeyDown);
    input?.removeEventListener('focus', this.render);
    input?.removeEventListener('blur', this.render);
    this.el.querySelector('.otp-input--control')?.removeEventListener('mousedown', this.onMouseDown);
    window.clearTimeout(this.submitTimer);
  }

  private get length(): number {
    return Number(this.inputEl?.dataset.length) || 6;
  }

  private get alphanumeric(): boolean {
    return this.inputEl?.dataset.alphanumeric !== undefined;
  }

  // A key typed or erased goes to the active cell; anything else — a paste,
  // the phone's code, a drop — is left to the browser and read whole after.
  private onBeforeInput = (event: InputEvent): void => {
    if (event.inputType === 'insertText' && event.data !== null) {
      event.preventDefault();
      const text = otpInputClean(event.data, this.length, this.alphanumeric);

      // A whole code typed at once is the code, wherever the cell stood.
      this.commit(text.length === this.length
        ? otpInputFrom(text, this.length)
        : otpInputWrite(this.state, text));
    } else if (event.inputType === 'deleteContentBackward' || event.inputType === 'deleteContentForward') {
      event.preventDefault();
      this.commit(otpInputErase(this.state, event.inputType === 'deleteContentBackward'));
    }
  };

  // What the browser wrote by itself replaces the code.
  private onInput = (): void => {
    const input = this.inputEl;

    if (!input || otpInputValue(this.state) === input.value) {
      return;
    }

    this.commit(otpInputFrom(otpInputClean(input.value, this.length, this.alphanumeric), this.length), false);
  };

  private onKeyDown = (event: KeyboardEvent): void => {
    const moves: Record<string, number> = {
      ArrowLeft: this.state.active - 1,
      ArrowRight: this.state.active + 1,
      Home: 0,
      End: this.length - 1,
    };

    if (event.key in moves) {
      event.preventDefault();
      this.state = otpInputMove(this.state, moves[event.key]);
      this.render();
    }
  };

  // The cell under the pointer becomes the active one, the input focused
  // without the browser placing a caret of its own.
  private onMouseDown = (event: MouseEvent): void => {
    const index = this.cellEls.findIndex((cell) => {
      const rect = cell.getBoundingClientRect();

      return event.clientX < rect.right;
    });

    event.preventDefault();
    this.state = otpInputMove(this.state, index === -1 ? this.length - 1 : index);
    this.inputEl?.focus();
    this.render();
  };

  private commit(state: OtpInputState, notify = true): void {
    const input = this.inputEl;

    if (!input) {
      return;
    }

    const wasComplete = otpInputComplete(this.state);
    this.state = state;
    input.value = otpInputValue(state);

    // Those listening to the field — its validation — hear what was written.
    if (notify) {
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    this.render();

    // Sent as soon as it is complete; a complete code corrected in place —
    // a refused one — once the hand rests, so that two digits changed in a row
    // are sent together. An agent filling it hands the submission back.
    window.clearTimeout(this.submitTimer);

    if (otpInputComplete(state) && !this.isAssisted && input.dataset.autoSubmit !== undefined) {
      if (wasComplete) {
        this.submitTimer = window.setTimeout(() => this.submit(input), OTP_INPUT_CORRECTION_DELAY);
      } else {
        this.submit(input);
      }
    }
  }

  // Through the form's own button, so it shows the code on its way — disabled,
  // loading — as when it is pressed.
  private submit(input: HTMLInputElement): void {
    const form = input.form;
    const button = Array.from(form?.elements ?? []).find(
      (element): element is HTMLButtonElement | HTMLInputElement =>
        (element instanceof HTMLButtonElement || element instanceof HTMLInputElement) && element.type === 'submit'
    );

    form?.requestSubmit(button);
  }

  private render = (): void => {
    const input = this.inputEl;

    if (!input) {
      return;
    }

    const focused = document.activeElement === input;

    // All of it selected: a paste or the phone's code replaces the code.
    if (focused) {
      input.setSelectionRange(0, input.value.length);
    }

    otpInputCells(this.state, focused).forEach((cell, index) => {
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
