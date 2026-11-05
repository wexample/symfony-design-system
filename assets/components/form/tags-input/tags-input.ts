import Field from '../../../js/Class/Field';
import {
  TAGS_SUGGESTIONS_DELAY_MS,
  TagsSuggestion,
  tagsFetchSuggestions,
  tagsFilterSuggestions,
  tagsJoin,
  tagsMergeSuggestions,
  tagsNormalizeSuggestions,
  tagsSplit
} from '../../../js/Helper/TagsHelper';

// The server field: tags drawn over the text input they came with. The input
// stays the field — posted with the form, spoken to by the form stack — and
// holds the tags joined by the separator; it is only set aside from sight.
// The words offered and what a press does are the vue's (TagsHelper).
export default class extends Field {
  private sourceEl: HTMLInputElement | null = null;
  private frameEl: HTMLElement | null = null;
  private listEl: HTMLElement | null = null;
  private entryEl: HTMLInputElement | null = null;
  private menuEl: HTMLElement | null = null;
  private suggestions: TagsSuggestion[] = [];
  private fetched: TagsSuggestion[] = [];
  private offered: TagsSuggestion[] = [];
  private active = -1;
  private focused = false;
  private fetchTimer: ReturnType<typeof setTimeout> | null = null;
  private fetchController: AbortController | null = null;
  private entryPlaceholder = '';

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.sourceEl = this.el.querySelector('input.tags-input--source');
    this.frameEl = this.el.querySelector('.tags-input--frame');
    this.listEl = this.el.querySelector('.tags-input--list');
    this.entryEl = this.el.querySelector('input.tags-input--entry');
    this.menuEl = this.el.querySelector('.tags-input--menu');

    if (!this.sourceEl || !this.frameEl || !this.listEl || !this.entryEl || !this.menuEl) {
      return;
    }

    try {
      this.suggestions = tagsNormalizeSuggestions(JSON.parse(this.el.dataset.suggestions ?? '[]'));
    } catch {
      this.suggestions = [];
    }

    this.entryPlaceholder = this.entryEl.placeholder;
    this.drawTags();
    this.frameEl.hidden = false;
    this.sourceEl.classList.add('tags-input--source-aside');
    // The label points at the entry, where a tag is typed.
    this.el.querySelector('.form--label')?.setAttribute('for', this.entryEl.id = `${this.sourceEl.id}-entry`);

    this.frameEl.addEventListener('click', this.onFrameClick);
    this.frameEl.addEventListener('mousedown', this.onFrameMousedown);
    this.entryEl.addEventListener('keydown', this.onEntryKeydown);
    this.entryEl.addEventListener('input', this.onEntryInput);
    this.entryEl.addEventListener('focus', this.onEntryFocus);
    this.entryEl.addEventListener('blur', this.onEntryBlur);
    this.menuEl.addEventListener('mousedown', this.onMenuMousedown);
    this.menuEl.addEventListener('mousemove', this.onMenuMousemove);
    this.sourceEl.addEventListener('input', this.onSourceInput);
  }

  protected async deactivateListeners(): Promise<void> {
    this.frameEl?.removeEventListener('click', this.onFrameClick);
    this.frameEl?.removeEventListener('mousedown', this.onFrameMousedown);
    this.entryEl?.removeEventListener('keydown', this.onEntryKeydown);
    this.entryEl?.removeEventListener('input', this.onEntryInput);
    this.entryEl?.removeEventListener('focus', this.onEntryFocus);
    this.entryEl?.removeEventListener('blur', this.onEntryBlur);
    this.menuEl?.removeEventListener('mousedown', this.onMenuMousedown);
    this.menuEl?.removeEventListener('mousemove', this.onMenuMousemove);
    this.sourceEl?.removeEventListener('input', this.onSourceInput);
    this.sourceEl?.classList.remove('tags-input--source-aside');
    if (this.fetchTimer) {
      clearTimeout(this.fetchTimer);
    }
    this.fetchController?.abort();

    await super.deactivateListeners();
  }

  private get separator(): string {
    return this.el.dataset.separator || ',';
  }

  private readTags(): string[] {
    return tagsSplit(this.sourceEl?.value ?? '', this.separator);
  }

  private writeTags(tags: string[]): void {
    if (!this.sourceEl) {
      return;
    }

    this.sourceEl.value = tagsJoin(tagsSplit(tags.join(this.separator), this.separator), this.separator);
    this.drawTags();
    this.drawMenu();
    this.notifyChanged(this.sourceEl);
  }

  private drawTags(): void {
    const template = this.el.querySelector<HTMLTemplateElement>('template.tags-input--tag-template');

    if (!this.listEl || !template) {
      return;
    }

    const removeLabel = this.el.dataset.removeLabel ?? '';
    const tags = this.readTags();

    // Said while nothing is held, as the vue says it.
    if (this.entryEl) {
      this.entryEl.placeholder = tags.length ? '' : this.entryPlaceholder;
    }

    this.listEl.replaceChildren(...tags.map((tag) => {
      const tagEl = template.content.firstElementChild!.cloneNode(true) as HTMLElement;
      const removeEl = tagEl.querySelector<HTMLButtonElement>('.tags-input--remove');

      tagEl.querySelector('.marker--label')!.textContent = tag;

      if (removeEl) {
        removeEl.dataset.tag = tag;
        removeEl.setAttribute('aria-label', `${removeLabel} ${tag}`.trim());
      }

      return tagEl;
    }));
  }

  private drawMenu(): void {
    const template = this.el.querySelector<HTMLTemplateElement>('template.tags-input--option-template');

    if (!this.menuEl || !this.entryEl || !template) {
      return;
    }

    this.offered = tagsFilterSuggestions(
      tagsMergeSuggestions(this.suggestions, this.fetched),
      this.readTags(),
      this.entryEl.value
    );
    this.active = Math.min(this.active, this.offered.length - 1);

    const open = this.focused && this.offered.length > 0;
    const suggestLabel = this.el.dataset.suggestLabel ?? '';

    this.menuEl.replaceChildren(...this.offered.map((suggestion, index) => {
      const optionEl = template.content.firstElementChild!.cloneNode(true) as HTMLElement;
      const valueEl = optionEl.querySelector<HTMLElement>('.tags-input--option-value');

      optionEl.id = `${this.menuEl!.id}-${index}`;
      optionEl.dataset.index = String(index);
      optionEl.classList.toggle('is-active', index === this.active);
      optionEl.setAttribute('aria-selected', index === this.active ? 'true' : 'false');
      optionEl.setAttribute('aria-label', suggestLabel.replace('%tag%', suggestion.label));
      optionEl.querySelector('.marker--label')!.textContent = suggestion.label;

      if (valueEl) {
        if (suggestion.label !== suggestion.value) {
          valueEl.textContent = suggestion.value;
        } else {
          valueEl.remove();
        }
      }

      return optionEl;
    }));

    this.menuEl.hidden = !open;
    this.entryEl.setAttribute('aria-expanded', open ? 'true' : 'false');

    if (open && this.active >= 0) {
      this.entryEl.setAttribute('aria-activedescendant', `${this.menuEl.id}-${this.active}`);
    } else {
      this.entryEl.removeAttribute('aria-activedescendant');
    }
  }

  // What is typed in the entry becomes tags, the separator splitting it.
  private commitEntry(): void {
    if (!this.entryEl?.value.trim()) {
      return;
    }

    const typed = this.entryEl.value;

    this.entryEl.value = '';
    this.writeTags([...this.readTags(), ...tagsSplit(typed, this.separator)]);
    this.askSource();
  }

  private take(index: number): void {
    const suggestion = this.offered[index];

    if (!suggestion || !this.entryEl) {
      return;
    }

    this.entryEl.value = '';
    this.active = -1;
    this.writeTags([...this.readTags(), suggestion.value]);
    this.entryEl.focus();
    // Nothing typed any more: the source is asked for what it offers then.
    this.askSource();
  }

  // The source asked once the typing pauses, the question before let go.
  private askSource(): void {
    const url = this.el.dataset.suggestionsUrl;

    if (!url || !this.entryEl) {
      return;
    }

    if (this.fetchTimer) {
      clearTimeout(this.fetchTimer);
    }

    this.fetchTimer = setTimeout(async () => {
      this.fetchController?.abort();
      this.fetchController = new AbortController();
      this.fetched = await tagsFetchSuggestions(url, this.entryEl?.value ?? '', this.fetchController.signal);
      this.drawMenu();
    }, TAGS_SUGGESTIONS_DELAY_MS);
  }

  private onEntryKeydown = (event: KeyboardEvent): void => {
    const count = this.offered.length;
    const open = this.menuEl ? !this.menuEl.hidden : false;

    if (event.key === 'ArrowDown' && count) {
      event.preventDefault();
      this.active = (this.active + 1) % count;
      this.drawMenu();
    } else if (event.key === 'ArrowUp' && count) {
      event.preventDefault();
      this.active = this.active <= 0 ? count - 1 : this.active - 1;
      this.drawMenu();
    } else if (event.key === 'Escape' && open) {
      event.preventDefault();
      this.focused = false;
      this.drawMenu();
    } else if (event.key === 'Enter' || event.key === this.separator) {
      // Enter adds a tag, it does not send the form.
      event.preventDefault();

      if (open && this.active >= 0) {
        this.take(this.active);
      } else {
        this.commitEntry();
      }
    } else if (event.key === 'Backspace' && this.entryEl?.value === '') {
      this.writeTags(this.readTags().slice(0, -1));
    }
  };

  private onEntryInput = (): void => {
    this.active = -1;
    this.focused = true;
    this.drawMenu();
    this.askSource();
  };

  private onEntryFocus = (): void => {
    this.focused = true;
    this.drawMenu();
    this.askSource();
  };

  private onEntryBlur = (): void => {
    this.focused = false;
    this.commitEntry();
    this.drawMenu();
  };

  // A tag's × pressed keeps the entry written in.
  private onFrameMousedown = (event: Event): void => {
    if ((event.target as HTMLElement).closest('.tags-input--remove')) {
      event.preventDefault();
    }
  };

  private onFrameClick = (event: Event): void => {
    const removeEl = (event.target as HTMLElement).closest<HTMLButtonElement>('.tags-input--remove');

    if (removeEl) {
      this.writeTags(this.readTags().filter((tag) => tag !== removeEl.dataset.tag));
    }

    this.entryEl?.focus();
  };

  // A word offered taken as it is pressed, before the entry loses the focus.
  private onMenuMousedown = (event: Event): void => {
    const optionEl = (event.target as HTMLElement).closest<HTMLElement>('.tags-input--option');

    event.preventDefault();

    if (optionEl) {
      this.take(Number(optionEl.dataset.index));
    }
  };

  private onMenuMousemove = (event: Event): void => {
    const optionEl = (event.target as HTMLElement).closest<HTMLElement>('.tags-input--option');
    const index = optionEl ? Number(optionEl.dataset.index) : -1;

    if (optionEl && index !== this.active) {
      this.active = index;
      this.drawMenu();
    }
  };

  // Something wrote into the input itself — the form stack filling it, an
  // assisted value: the tags show what the field now holds.
  private onSourceInput = (): void => {
    this.drawTags();
  };
}
