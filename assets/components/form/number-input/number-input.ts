import Field from '../../../js/Class/Field';
import { numberParse } from '../../../js/Helper/NumberHelper';

// A number field takes a figure pasted as it was written elsewhere — « 1,5 »
// from a French text, « 1 234,50 » from a spreadsheet —, which the browser
// would otherwise drop, a number control knowing the point alone.
export default class extends Field {
  private inputEl: HTMLInputElement | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.inputEl = this.el.querySelector('input[type="number"]');
    this.inputEl?.addEventListener('paste', this.onPaste);
  }

  protected async deactivateListeners(): Promise<void> {
    this.inputEl?.removeEventListener('paste', this.onPaste);

    await super.deactivateListeners();
  }

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
