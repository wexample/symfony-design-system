import Field from '../../../js/Class/Field';

// The server field: the lines are posted as they are written, each under its
// index. The script keeps a blank line last — the one before written in, a new
// one opens — and takes a pair off with its ×, the indexes written again so
// the list posted has no gap.
export default class extends Field {
  private frameEl: HTMLElement | null = null;
  private fieldId: string | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.frameEl = this.el.querySelector('.key-value-input--frame');

    if (!this.frameEl) {
      return;
    }

    this.fieldId = this.frameEl.querySelector<HTMLInputElement>('.key-value-input--key')?.id || null;
    this.el.classList.add('is-active');
    this.frameEl.addEventListener('input', this.onInput);
    this.frameEl.addEventListener('click', this.onClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.frameEl?.removeEventListener('input', this.onInput);
    this.frameEl?.removeEventListener('click', this.onClick);
    this.el.classList.remove('is-active');

    await super.deactivateListeners();
  }

  private get lines(): HTMLElement[] {
    return [...(this.frameEl?.querySelectorAll<HTMLElement>('.key-value-input--pair') ?? [])];
  }

  // Written in the last line: it is a pair now, and a new last line opens.
  private onInput = (event: Event): void => {
    const line = (event.target as HTMLElement).closest<HTMLElement>('.key-value-input--pair');

    if (!line || !line.classList.contains('key-value-input--new')) {
      return;
    }

    const mark = line.querySelector('.key-value-input--mark');
    const next = this.createLine();

    line.classList.remove('key-value-input--new');
    mark?.replaceWith(this.createRemove());

    // The + goes on to the new last line.
    if (mark) {
      next.append(mark);
    }

    this.frameEl?.append(next);
    this.renumber();
  };

  private onClick = (event: Event): void => {
    const remove = (event.target as HTMLElement).closest('.key-value-input--remove');
    const line = remove?.closest<HTMLElement>('.key-value-input--pair');

    if (!line) {
      return;
    }

    const next = line.nextElementSibling?.querySelector<HTMLInputElement>('.key-value-input--key');

    line.remove();
    this.renumber();
    next?.focus();

    const input = this.frameEl?.querySelector<HTMLInputElement>('.key-value-input--key');

    if (input) {
      this.notifyChanged(input);
    }
  };

  private createLine(): HTMLElement {
    const line = document.createElement('div');
    line.className = 'key-value-input--pair key-value-input--new';
    line.append(this.createInput('key'), this.createInput('value'));

    return line;
  }

  private createInput(field: 'key' | 'value'): HTMLInputElement {
    const template = this.frameEl?.querySelector<HTMLInputElement>(`.key-value-input--new .key-value-input--${field}`)
      ?? this.frameEl?.querySelector<HTMLInputElement>(`.key-value-input--${field}`);
    const input = document.createElement('input');

    input.type = 'text';
    input.className = `key-value-input--${field}`;
    input.setAttribute('aria-label', this.el.dataset[field === 'key' ? 'keyLabel' : 'valueLabel'] ?? field);
    input.placeholder = template?.placeholder ?? '';

    return input;
  }

  private createRemove(): Element {
    const template = this.el.querySelector<HTMLTemplateElement>('template.key-value-input--remove-template');

    return template?.content.firstElementChild?.cloneNode(true) as Element ?? document.createElement('span');
  }

  // Each line posted under its place, the first one holding the field's id.
  private renumber(): void {
    const name = this.el.dataset.name ?? '';

    this.lines.forEach((line, index) => {
      line.dataset.pair = String(index);

      const key = line.querySelector<HTMLInputElement>('.key-value-input--key');

      if (key && this.fieldId) {
        if (index === 0) {
          key.id = this.fieldId;
        } else if (key.id === this.fieldId) {
          key.removeAttribute('id');
        }
      }

      if (!name) {
        return;
      }

      line.querySelector<HTMLInputElement>('.key-value-input--key')?.setAttribute('name', `${name}[${index}][key]`);
      line.querySelector<HTMLInputElement>('.key-value-input--value')?.setAttribute('name', `${name}[${index}][value]`);
    });
  }
}
