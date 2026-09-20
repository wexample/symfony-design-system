import Component from '@wexample/symfony-loader/js/Class/Component';
import OverlayService from '@wexample/symfony-loader/js/Services/OverlayService';
import KeyboardService from '@wexample/symfony-loader/js/Services/KeyboardService';

export default class extends Component {
  public overlayUseBackdrop = false;

  private triggerEl?: HTMLButtonElement;
  private dropdownEl?: HTMLElement;
  private searchEl?: HTMLInputElement;
  private listEl?: HTMLElement;
  private hiddenEl?: HTMLInputElement;
  private overlayService?: OverlayService;
  private keyboardService?: KeyboardService;

  protected async activateListeners(): Promise<void> {
    this.triggerEl  = this.el.querySelector('.select--trigger') as HTMLButtonElement;
    this.dropdownEl = this.el.querySelector('.select--dropdown') as HTMLElement;
    this.searchEl   = this.el.querySelector('.select--search-input') as HTMLInputElement;
    this.listEl     = this.el.querySelector('.select--list') as HTMLElement;
    this.hiddenEl   = this.el.querySelector('input[type="hidden"]') as HTMLInputElement;

    this.overlayService  = this.app.getServiceOrFail(OverlayService) as OverlayService;
    this.keyboardService = this.app.getServiceOrFail(KeyboardService) as KeyboardService;

    this.overlayService.register(this);

    this.triggerEl?.addEventListener('click', this.onTriggerClick);
    this.searchEl?.addEventListener('input', this.onSearch);
    this.listEl?.addEventListener('click', this.onOptionClick);

    this.keyboardService.registerKeyDown(this, KeyboardService.KEY_ESCAPE, () => {
      if (this.overlayIsOpen()) {
        this.close();
        return true;
      }
      return false;
    });
  }

  protected async deactivateListeners(): Promise<void> {
    this.overlayService?.unregister(this);
    this.keyboardService?.unregisterOwner(this);

    this.triggerEl?.removeEventListener('click', this.onTriggerClick);
    this.searchEl?.removeEventListener('input', this.onSearch);
    this.listEl?.removeEventListener('click', this.onOptionClick);
  }

  public overlayIsOpen(): boolean {
    return this.dropdownEl ? !this.dropdownEl.hidden : false;
  }

  public overlayGetElement(): HTMLElement | null {
    return this.dropdownEl || null;
  }

  public overlayGetFocusTarget(): HTMLElement | null {
    return this.searchEl || null;
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
    // Focus is handled by OverlayService via overlayGetFocusTarget()
  }

  private close(): void {
    if (!this.dropdownEl) return;
    this.dropdownEl.hidden = true;
    this.triggerEl?.setAttribute('aria-expanded', 'false');
    if (this.searchEl) this.searchEl.value = '';
    this.filterOptions('');
    this.overlayService?.clearActive(this);
  }

  private onSearch = (): void => {
    this.filterOptions(this.searchEl?.value ?? '');
  };

  private filterOptions(query: string): void {
    const q = query.toLowerCase();
    this.listEl?.querySelectorAll<HTMLElement>('.select--option').forEach((opt) => {
      opt.hidden = !opt.textContent?.toLowerCase().includes(q);
    });
  }

  private onOptionClick = (e: Event): void => {
    const option = (e.target as HTMLElement).closest<HTMLElement>('.select--option');
    if (!option) return;

    const value = option.dataset.value ?? '';
    const label = option.textContent?.trim() ?? '';

    this.listEl?.querySelectorAll('.select--option').forEach((opt) => {
      opt.classList.remove('select--option--selected');
      opt.setAttribute('aria-selected', 'false');
    });
    option.classList.add('select--option--selected');
    option.setAttribute('aria-selected', 'true');

    const labelEl = this.triggerEl?.querySelector('.select--trigger-label');
    if (labelEl) labelEl.textContent = label;
    if (this.hiddenEl) this.hiddenEl.value = value;
    this.el.dataset.value = value;

    this.el.dispatchEvent(new CustomEvent('select:change', { bubbles: true, detail: { value } }));
    this.close();
  };
}
