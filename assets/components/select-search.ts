import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private triggerEl?: HTMLButtonElement;
  private dropdownEl?: HTMLElement;
  private searchEl?: HTMLInputElement;
  private listEl?: HTMLElement;
  private hiddenEl?: HTMLInputElement;

  protected async activateListeners(): Promise<void> {
    this.triggerEl  = this.el.querySelector('.select--trigger') as HTMLButtonElement;
    this.dropdownEl = this.el.querySelector('.select--dropdown') as HTMLElement;
    this.searchEl   = this.el.querySelector('.select--search-input') as HTMLInputElement;
    this.listEl     = this.el.querySelector('.select--list') as HTMLElement;
    this.hiddenEl   = this.el.querySelector('input[type="hidden"]') as HTMLInputElement;

    this.triggerEl?.addEventListener('click', this.onTriggerClick);
    this.searchEl?.addEventListener('input', this.onSearch);
    this.listEl?.addEventListener('click', this.onOptionClick);
    document.addEventListener('click', this.onOutsideClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.triggerEl?.removeEventListener('click', this.onTriggerClick);
    this.searchEl?.removeEventListener('input', this.onSearch);
    this.listEl?.removeEventListener('click', this.onOptionClick);
    document.removeEventListener('click', this.onOutsideClick);
  }

  private onTriggerClick = (e: Event): void => {
    e.stopPropagation();
    this.dropdownEl?.hidden ? this.open() : this.close();
  };

  private open(): void {
    if (!this.dropdownEl) return;
    this.dropdownEl.hidden = false;
    this.triggerEl?.setAttribute('aria-expanded', 'true');
    this.searchEl?.focus();
  }

  private close(): void {
    if (!this.dropdownEl) return;
    this.dropdownEl.hidden = true;
    this.triggerEl?.setAttribute('aria-expanded', 'false');
    if (this.searchEl) this.searchEl.value = '';
    this.filterOptions('');
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

  private onOutsideClick = (e: Event): void => {
    if (!this.el.contains(e.target as Node)) this.close();
  };
}
