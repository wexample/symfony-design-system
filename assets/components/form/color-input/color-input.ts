import Field from '../../../js/Class/Field';
import { colorNormalize } from '../../../js/Helper/ColorHelper';

// The server field: the hex is the field — posted with the form, spoken to by
// the form stack —, the swatch and the colours offered kept one colour with
// it, as the vue keeps them (ColorHelper).
export default class extends Field {
  private rootEl: HTMLElement | null = null;
  private hexEl: HTMLInputElement | null = null;
  private pickerEl: HTMLInputElement | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.rootEl = this.el.querySelector('.color-input');
    this.hexEl = this.el.querySelector('input.color-input--hex');
    this.pickerEl = this.el.querySelector('input.color-input--picker');

    this.hexEl?.addEventListener('input', this.onHexInput);
    this.hexEl?.addEventListener('blur', this.onHexBlur);
    this.pickerEl?.addEventListener('input', this.onPick);
    this.el.querySelector('.color-input--swatches')?.addEventListener('click', this.onOffer);
  }

  protected async deactivateListeners(): Promise<void> {
    this.hexEl?.removeEventListener('input', this.onHexInput);
    this.hexEl?.removeEventListener('blur', this.onHexBlur);
    this.pickerEl?.removeEventListener('input', this.onPick);
    this.el.querySelector('.color-input--swatches')?.removeEventListener('click', this.onOffer);

    await super.deactivateListeners();
  }

  // The swatch, the picker and the colours offered say what the hex holds.
  private draw(): void {
    const shown = colorNormalize(this.hexEl?.value) || '';

    this.rootEl?.classList.toggle('is-empty', !shown);

    if (shown) {
      this.rootEl?.style.setProperty('--color-input-value', shown);

      if (this.pickerEl) {
        this.pickerEl.value = shown;
      }
    } else {
      this.rootEl?.style.removeProperty('--color-input-value');
    }

    this.el.querySelectorAll<HTMLElement>('.color-input--offer').forEach((offer) => {
      const active = offer.dataset.value === shown;

      offer.classList.toggle('is-active', active);
      offer.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  private write(value: string): void {
    if (!this.hexEl) {
      return;
    }

    this.hexEl.value = value;
    this.draw();
    this.notifyChanged(this.hexEl);
  }

  private onHexInput = (): void => {
    this.draw();
  };

  // Left, what is typed is read as a colour where it is one.
  private onHexBlur = (): void => {
    const normalized = colorNormalize(this.hexEl?.value);

    if (normalized !== null && normalized !== this.hexEl?.value) {
      this.write(normalized);
    }
  };

  private onPick = (): void => {
    this.write(colorNormalize(this.pickerEl?.value) ?? '');
  };

  private onOffer = (event: Event): void => {
    const offer = (event.target as HTMLElement).closest<HTMLElement>('.color-input--offer');

    if (offer?.dataset.value) {
      this.write(offer.dataset.value);
    }
  };
}
