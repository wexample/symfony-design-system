import Field from '../../js/Class/Field';
import OverlayService from '@wexample/symfony-design-system/js/Services/OverlayService';
import KeyboardService from '@wexample/symfony-loader/js/Services/KeyboardService';
import {
  ASSISTANCE_STEP_DELAY_MS,
  assistanceWait,
  type AssistanceWriteOptions,
} from '@wexample/js-api/Helper/Assistance';

export default class extends Field {
  public overlayUseBackdrop = false;

  private triggerEl?: HTMLButtonElement;
  private dropdownEl?: HTMLElement;
  private listEl?: HTMLElement;
  private hiddenEl?: HTMLSelectElement;
  private overlayService?: OverlayService;
  private keyboardService?: KeyboardService;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.triggerEl  = this.el.querySelector('.select--trigger') as HTMLButtonElement;
    this.dropdownEl = this.el.querySelector('.select--dropdown') as HTMLElement;
    this.listEl     = this.el.querySelector('.select--list') as HTMLElement;
    this.hiddenEl   = this.el.querySelector('select.select--native') as HTMLSelectElement;

    this.overlayService  = this.app.getServiceOrFail(OverlayService) as OverlayService;
    this.keyboardService = this.app.getServiceOrFail(KeyboardService) as KeyboardService;

    this.overlayService.register(this);

    this.triggerEl?.addEventListener('click', this.onTriggerClick);
    this.listEl?.addEventListener('click', this.onOptionClick);

    this.keyboardService.registerKeyDown(this, KeyboardService.KEY_ESCAPE, () => {
      if (this.overlayIsOpen()) {
        this.close();
        return true;
      }
      return false;
    });

    // Sync custom UI from native select's current value (handles browser auto-selection of first option).
    const initialValue = this.hiddenEl?.value;
    if (initialValue) {
      this.applySelection(initialValue);
    }
  }

  protected async deactivateListeners(): Promise<void> {
    this.overlayService?.unregister(this);
    this.keyboardService?.unregisterOwner(this);

    this.triggerEl?.removeEventListener('click', this.onTriggerClick);
    this.listEl?.removeEventListener('click', this.onOptionClick);
  }

  public overlayIsOpen(): boolean {
    return this.dropdownEl ? !this.dropdownEl.hidden : false;
  }

  public overlayGetElement(): HTMLElement | null {
    return this.dropdownEl || null;
  }

  public overlayGetFocusTarget(): HTMLElement | null {
    return this.triggerEl || null;
  }

  public overlayOnClickOutside(_event: MouseEvent): void {
    this.close();
  }

  // When the dropdown is already open, OverlayService.onDocumentMouseDown fires
  // before this click event and calls overlayOnClickOutside → close().
  // So here we only need to handle the "closed → open" case.
  private onTriggerClick = (): void => {
    if (!this.overlayIsOpen()) {
      this.open();
    }
  };

  private open(): void {
    if (!this.dropdownEl) return;
    this.dropdownEl.hidden = false;
    this.triggerEl?.setAttribute('aria-expanded', 'true');
    this.overlayService?.setActive(this);
  }

  private close(): void {
    if (!this.dropdownEl) return;
    this.dropdownEl.hidden = true;
    this.triggerEl?.setAttribute('aria-expanded', 'false');
    this.overlayService?.clearActive(this);
  }

  private onOptionClick = (e: Event): void => {
    const option = (e.target as HTMLElement).closest<HTMLElement>('.select--option');
    if (!option) return;

    const value = option.dataset.value ?? '';
    this.applySelection(value);
    this.el.dispatchEvent(new CustomEvent('select:change', { bubbles: true, detail: { value } }));
    this.close();
  };

  /**
   * An option is not spelled out. The list opens, the option is taken, the list
   * closes — what a person would have been seen doing, at a pace that can be
   * followed.
   */
  protected async writeValueAssisted(
    value: unknown,
    options: AssistanceWriteOptions
  ): Promise<void> {
    const target = String(value ?? '');
    const delay = options.delayMs === 0 ? 0 : ASSISTANCE_STEP_DELAY_MS;

    this.open();
    await assistanceWait(delay, options.signal);

    this.applySelection(target);
    this.el.dispatchEvent(
      new CustomEvent('select:change', { bubbles: true, detail: { value: target } })
    );

    await assistanceWait(delay, options.signal);
    this.close();
  }

  private applySelection(value: string): void {
    this.listEl?.querySelectorAll<HTMLElement>('.select--option').forEach((opt) => {
      const isSelected = opt.dataset.value === value;
      opt.classList.toggle('select--option--selected', isSelected);
      opt.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });

    const selectedOpt = this.listEl?.querySelector<HTMLElement>(`.select--option[data-value="${CSS.escape(value)}"]`);
    const label = selectedOpt?.textContent?.trim() ?? '';

    const labelEl = this.triggerEl?.querySelector('.select--trigger-label');
    if (labelEl) labelEl.textContent = label;
    if (this.hiddenEl) this.hiddenEl.value = value;
    this.el.dataset.value = value;
  }
}
