import Field from '../../../js/Class/Field';

// The server field: tags drawn over the text input they came with. The input
// stays the field — posted with the form, spoken to by the form stack — and
// holds the tags joined by the separator; it is only set aside from sight.
export default class extends Field {
  private sourceEl: HTMLInputElement | null = null;
  private frameEl: HTMLElement | null = null;
  private listEl: HTMLElement | null = null;
  private entryEl: HTMLInputElement | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.sourceEl = this.el.querySelector('input.tags-input--source');
    this.frameEl = this.el.querySelector('.tags-input--frame');
    this.listEl = this.el.querySelector('.tags-input--list');
    this.entryEl = this.el.querySelector('input.tags-input--entry');

    if (!this.sourceEl || !this.frameEl || !this.listEl || !this.entryEl) {
      return;
    }

    this.drawTags();
    this.frameEl.hidden = false;
    this.sourceEl.classList.add('tags-input--source-aside');
    // The label points at the entry, where a tag is typed.
    this.el.querySelector('.form--label')?.setAttribute('for', this.entryEl.id = `${this.sourceEl.id}-entry`);

    this.frameEl.addEventListener('click', this.onFrameClick);
    this.entryEl.addEventListener('keydown', this.onEntryKeydown);
    this.entryEl.addEventListener('blur', this.onEntryBlur);
    this.sourceEl.addEventListener('input', this.onSourceInput);
  }

  protected async deactivateListeners(): Promise<void> {
    this.frameEl?.removeEventListener('click', this.onFrameClick);
    this.entryEl?.removeEventListener('keydown', this.onEntryKeydown);
    this.entryEl?.removeEventListener('blur', this.onEntryBlur);
    this.sourceEl?.removeEventListener('input', this.onSourceInput);
    this.sourceEl?.classList.remove('tags-input--source-aside');

    await super.deactivateListeners();
  }

  private get separator(): string {
    return this.options?.separator ?? ',';
  }

  private readTags(): string[] {
    return this.splitTags(this.sourceEl?.value ?? '');
  }

  private splitTags(text: string): string[] {
    return [...new Set(text.split(this.separator).map((tag) => tag.trim()).filter(Boolean))];
  }

  private writeTags(tags: string[]): void {
    if (!this.sourceEl) {
      return;
    }

    this.sourceEl.value = tags.join(`${this.separator} `);
    this.drawTags();
    this.notifyChanged(this.sourceEl);
  }

  private drawTags(): void {
    if (!this.listEl) {
      return;
    }

    const removeLabel = this.listEl.dataset.removeLabel ?? '';
    this.listEl.replaceChildren(...this.readTags().map((tag) => {
      const tagEl = document.createElement('span');
      tagEl.className = 'tags-input--tag';
      tagEl.textContent = tag;

      const removeEl = document.createElement('button');
      removeEl.type = 'button';
      removeEl.className = 'tags-input--remove';
      removeEl.dataset.tag = tag;
      removeEl.setAttribute('aria-label', `${removeLabel} ${tag}`.trim());
      removeEl.textContent = '×';
      tagEl.append(removeEl);

      return tagEl;
    }));
  }

  // What is typed in the entry becomes tags, the separator splitting it.
  private commitEntry(): void {
    if (!this.entryEl?.value.trim()) {
      return;
    }

    this.writeTags(this.splitTags([...this.readTags(), this.entryEl.value].join(this.separator)));
    this.entryEl.value = '';
  }

  private onEntryKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Enter' || event.key === this.separator) {
      // Enter adds a tag, it does not send the form.
      event.preventDefault();
      this.commitEntry();
    } else if (event.key === 'Backspace' && this.entryEl?.value === '') {
      this.writeTags(this.readTags().slice(0, -1));
    }
  };

  private onEntryBlur = (): void => {
    this.commitEntry();
  };

  private onFrameClick = (event: Event): void => {
    const removeEl = (event.target as HTMLElement).closest<HTMLButtonElement>('.tags-input--remove');

    if (removeEl) {
      this.writeTags(this.readTags().filter((tag) => tag !== removeEl.dataset.tag));
    }

    this.entryEl?.focus();
  };

  // Something wrote into the input itself — the form stack filling it, an
  // assisted value: the tags show what the field now holds.
  private onSourceInput = (): void => {
    this.drawTags();
  };
}
