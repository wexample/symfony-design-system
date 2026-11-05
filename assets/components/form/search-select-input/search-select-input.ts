import Field from '../../../js/Class/Field';
import {
  Option,
  OPTIONS_SOURCE_DELAY_MS,
  optionsFetch,
  optionsFilter,
  optionsMerge,
  optionsNormalize
} from '../../../js/Helper/OptionsHelper';

type Row = Option & { none?: boolean };

// The server field: the hidden input is the field — posted with the form,
// spoken to by the form stack —, the trigger shows the option chosen by its
// label, the list under it searched as the vue searches it (OptionsHelper).
export default class extends Field {
  private rootEl: HTMLElement | null = null;
  private triggerEl: HTMLButtonElement | null = null;
  private labelEl: HTMLElement | null = null;
  private dropdownEl: HTMLElement | null = null;
  private searchEl: HTMLInputElement | null = null;
  private listEl: HTMLElement | null = null;
  private statusEl: HTMLElement | null = null;
  private valueEl: HTMLInputElement | null = null;
  private options: Option[] = [];
  private fetched: Option[] = [];
  private rows: Row[] = [];
  private active = -1;
  private loading = false;
  private placeholder = '';
  private fetchTimer: ReturnType<typeof setTimeout> | null = null;
  private fetchController: AbortController | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.rootEl = this.el.querySelector('.search-select-input');
    this.triggerEl = this.el.querySelector('.select--trigger');
    this.labelEl = this.el.querySelector('.select--trigger-label');
    this.dropdownEl = this.el.querySelector('.select--dropdown');
    this.searchEl = this.el.querySelector('.select--search-input');
    this.listEl = this.el.querySelector('.select--list');
    this.statusEl = this.el.querySelector('.select--status');
    this.valueEl = this.el.querySelector('input.search-select-input--value');

    if (!this.rootEl || !this.triggerEl || !this.dropdownEl || !this.searchEl || !this.listEl || !this.valueEl) {
      return;
    }

    try {
      this.options = optionsNormalize(JSON.parse(this.rootEl.dataset.options ?? '[]'));
    } catch {
      this.options = [];
    }

    this.placeholder = this.labelEl?.classList.contains('is-placeholder') ? (this.labelEl.textContent ?? '') : '';

    this.triggerEl.addEventListener('click', this.onTriggerClick);
    this.triggerEl.addEventListener('keydown', this.onTriggerKeydown);
    this.searchEl.addEventListener('input', this.onSearchInput);
    this.searchEl.addEventListener('keydown', this.onSearchKeydown);
    this.listEl.addEventListener('pointerdown', this.onListPointerdown);
    this.listEl.addEventListener('mousemove', this.onListMousemove);
    document.addEventListener('pointerdown', this.onDocumentPointerdown);

    this.resolveLabel();
  }

  // A value no option given holds and no label given says: the source asked
  // for it, as the vue asks it.
  private async resolveLabel(): Promise<void> {
    const url = this.rootEl?.dataset.optionsUrl;
    const value = this.value;

    if (!url || !value || this.rootEl?.dataset.valueLabel !== value || this.options.some((option) => option.value === value)) {
      return;
    }

    const option = (await optionsFetch(url, value)).find((candidate) => candidate.value === value);

    if (option && value === this.value && this.labelEl && this.rootEl) {
      this.labelEl.textContent = option.label;
      this.rootEl.dataset.valueLabel = option.label;
    }
  }

  protected async deactivateListeners(): Promise<void> {
    this.triggerEl?.removeEventListener('click', this.onTriggerClick);
    this.triggerEl?.removeEventListener('keydown', this.onTriggerKeydown);
    this.searchEl?.removeEventListener('input', this.onSearchInput);
    this.searchEl?.removeEventListener('keydown', this.onSearchKeydown);
    this.listEl?.removeEventListener('pointerdown', this.onListPointerdown);
    this.listEl?.removeEventListener('mousemove', this.onListMousemove);
    document.removeEventListener('pointerdown', this.onDocumentPointerdown);

    if (this.fetchTimer) {
      clearTimeout(this.fetchTimer);
    }
    this.fetchController?.abort();

    await super.deactivateListeners();
  }

  private get isOpen(): boolean {
    return this.dropdownEl ? !this.dropdownEl.hidden : false;
  }

  private get required(): boolean {
    return this.rootEl?.dataset.required === 'true';
  }

  private get value(): string {
    return this.valueEl?.value ?? '';
  }

  private openList(): void {
    if (!this.dropdownEl || this.isOpen) {
      return;
    }

    this.dropdownEl.hidden = false;
    this.rootEl?.classList.add('is-open');
    this.triggerEl?.setAttribute('aria-expanded', 'true');
    this.searchEl!.value = '';
    this.drawList();
    this.active = Math.max(0, this.rows.findIndex((row) => row.value === this.value));
    this.drawList();
    this.askSource();
    this.searchEl?.focus();
  }

  private closeList(focusTrigger = false): void {
    if (!this.dropdownEl) {
      return;
    }

    this.dropdownEl.hidden = true;
    this.rootEl?.classList.remove('is-open');
    this.triggerEl?.setAttribute('aria-expanded', 'false');

    if (focusTrigger) {
      this.triggerEl?.focus();
    }
  }

  private drawList(): void {
    if (!this.listEl || !this.searchEl) {
      return;
    }

    const query = this.searchEl.value;
    const offered = optionsFilter(optionsMerge(this.options, this.fetched), query);
    const none = !this.required && !query.trim() ? [{ value: '', label: this.rootEl?.dataset.noneLabel ?? '—', none: true }] : [];

    this.rows = [...none, ...offered];
    // An option is always pointed at while there is one: Enter takes it.
    this.active = this.rows.length ? Math.min(Math.max(this.active, 0), this.rows.length - 1) : -1;

    this.listEl.replaceChildren(...this.rows.map((row, index) => {
      const item = document.createElement('li');

      item.id = `${this.listEl!.id}-${index}`;
      item.className = 'select--option';
      item.classList.toggle('select--option--empty', Boolean(row.none));
      item.classList.toggle('select--option--selected', row.value === this.value);
      item.classList.toggle('is-active', index === this.active);
      item.setAttribute('role', 'option');
      item.setAttribute('aria-selected', row.value === this.value ? 'true' : 'false');
      item.dataset.index = String(index);
      item.textContent = row.label;

      return item;
    }));

    if (this.active >= 0) {
      this.searchEl.setAttribute('aria-activedescendant', `${this.listEl.id}-${this.active}`);
    } else {
      this.searchEl.removeAttribute('aria-activedescendant');
    }

    if (this.statusEl) {
      const anything = this.rows.some((row) => !row.none);
      const status = this.loading && !anything
        ? (this.rootEl?.dataset.loadingLabel ?? '')
        : (anything ? '' : (this.rootEl?.dataset.emptyLabel ?? ''));

      this.statusEl.textContent = status;
      this.statusEl.hidden = !status;
    }
  }

  private choose(row: Row): void {
    if (!this.valueEl) {
      return;
    }

    this.valueEl.value = row.value;

    if (this.labelEl) {
      this.labelEl.textContent = row.none ? this.placeholder : row.label;
      this.labelEl.classList.toggle('is-placeholder', Boolean(row.none) || !row.value);
    }

    if (this.rootEl) {
      this.rootEl.dataset.valueLabel = row.none ? '' : row.label;
    }

    this.notifyChanged(this.valueEl);
    this.closeList(true);
  }

  // The source asked once the typing pauses, the question before let go.
  private askSource(): void {
    const url = this.rootEl?.dataset.optionsUrl;

    if (!url) {
      return;
    }

    if (this.fetchTimer) {
      clearTimeout(this.fetchTimer);
    }

    this.loading = true;
    this.drawList();
    this.fetchTimer = setTimeout(async () => {
      this.fetchController?.abort();
      this.fetchController = new AbortController();
      this.fetched = await optionsFetch(url, this.searchEl?.value ?? '', this.fetchController.signal);
      this.loading = false;
      this.drawList();
    }, OPTIONS_SOURCE_DELAY_MS);
  }

  private onTriggerClick = (): void => {
    if (this.isOpen) {
      this.closeList();
    } else {
      this.openList();
    }
  };

  private onTriggerKeydown = (event: KeyboardEvent): void => {
    if (['ArrowDown', 'Enter', ' '].includes(event.key)) {
      event.preventDefault();
      this.openList();
    }
  };

  private onSearchInput = (): void => {
    this.active = 0;
    this.drawList();
    this.askSource();
  };

  private onSearchKeydown = (event: KeyboardEvent): void => {
    const count = this.rows.length;

    if (event.key === 'ArrowDown' && count) {
      event.preventDefault();
      this.active = (this.active + 1) % count;
      this.drawList();
    } else if (event.key === 'ArrowUp' && count) {
      event.preventDefault();
      this.active = this.active <= 0 ? count - 1 : this.active - 1;
      this.drawList();
    } else if (event.key === 'Enter') {
      // Enter takes the option pointed at; it does not send the form.
      event.preventDefault();

      if (this.rows[this.active]) {
        this.choose(this.rows[this.active]);
      }
    } else if (event.key === 'Escape') {
      event.preventDefault();
      this.closeList(true);
    } else if (event.key === 'Tab') {
      this.closeList();
    }
  };

  private onListPointerdown = (event: Event): void => {
    const item = (event.target as HTMLElement).closest<HTMLElement>('.select--option');

    event.preventDefault();

    if (item) {
      this.choose(this.rows[Number(item.dataset.index)]);
    }
  };

  private onListMousemove = (event: Event): void => {
    const item = (event.target as HTMLElement).closest<HTMLElement>('.select--option');
    const index = item ? Number(item.dataset.index) : -1;

    if (item && index !== this.active) {
      this.active = index;
      this.drawList();
    }
  };

  private onDocumentPointerdown = (event: Event): void => {
    if (this.isOpen && this.rootEl && !this.rootEl.contains(event.target as Node)) {
      this.closeList();
    }
  };

  // What an agent reads and sets: the value and its label.
  public getAgentState(): Record<string, any> {
    return { ...super.getAgentState(), value: this.value, label: this.rootEl?.dataset.valueLabel ?? this.value };
  }
}
