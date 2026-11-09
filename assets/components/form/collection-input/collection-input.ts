import Field from '../../../js/Class/Field';

// The server field: each item posted under its index, as drawn. The script
// adds one from the prototype — its index put where the prototype holds
// `prototype_name` —, takes one off, moves one up or down; then posts every
// item again under its place in the order shown, so the list read back is
// the list seen. The arrows at either end do nothing, and say so.
//
// An item added is its entry's html: a field whose widget is a component of
// its own (a code editor) is drawn, not started — the vue field is the one
// for those.
export default class extends Field {
  private itemsEl: HTMLElement | null = null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.itemsEl = this.el.querySelector('.collection-input--items');
    this.el.addEventListener('click', this.onClick);
    this.renumber();
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onClick);

    await super.deactivateListeners();
  }

  private get items(): HTMLElement[] {
    return [...(this.itemsEl?.querySelectorAll<HTMLElement>(':scope > .collection-input--item') ?? [])];
  }

  private onClick = (event: Event): void => {
    const target = event.target as HTMLElement;

    if (target.closest('.collection-input--add')) {
      this.add();

      return;
    }

    const item = target.closest<HTMLElement>('.collection-input--item');

    if (!item || item.parentElement !== this.itemsEl) {
      return;
    }

    if (target.closest('.collection-input--remove')) {
      item.remove();
    } else if (target.closest('.collection-input--up')) {
      item.previousElementSibling?.before(item);
    } else if (target.closest('.collection-input--down')) {
      item.nextElementSibling?.after(item);
    } else {
      return;
    }

    this.renumber();
    this.notifyChanged(this.el);
  };

  private add(): void {
    const prototype = this.el.querySelector<HTMLElement>('.collection-input--prototype');
    const index = this.items.length;
    const html = prototype?.innerHTML.split(this.el.dataset.prototypeName ?? '__name__').join(String(index)) ?? '';
    const holder = document.createElement('div');

    holder.innerHTML = html.trim();

    const item = holder.firstElementChild as HTMLElement | null;

    if (!item || !this.itemsEl) {
      return;
    }

    item.dataset.index = String(index);
    this.itemsEl.append(item);
    this.renumber();
    item.querySelector<HTMLElement>('input, textarea, select')?.focus();
  }

  // Each item's names and ids moved to its place; the arrows of the ends
  // disabled, none while there is one.
  private renumber(): void {
    const name = this.el.dataset.name ?? '';
    const id = this.el.dataset.id ?? '';
    const items = this.items;

    items.forEach((item, index) => {
      const was = item.dataset.index ?? String(index);

      if (was !== String(index)) {
        // `x_1` and not `x_10`: an id's index ends where its digits do.
        const ids = new RegExp(`${id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}_${was}(?!\\d)`, 'g');

        item.querySelectorAll<HTMLElement>('*').forEach((el) => {
          ['name', 'id', 'for', 'aria-describedby', 'aria-labelledby'].forEach((attribute) => {
            const value = el.getAttribute(attribute);

            if (value !== null) {
              el.setAttribute(attribute, value
                .split(`${name}[${was}]`).join(`${name}[${index}]`)
                .replace(ids, `${id}_${index}`));
            }
          });
        });
        item.dataset.index = String(index);
      }

      const up = item.querySelector<HTMLButtonElement>(':scope > .collection-input--actions .collection-input--up');
      const down = item.querySelector<HTMLButtonElement>(':scope > .collection-input--actions .collection-input--down');

      [up, down].forEach((button) => button && (button.hidden = items.length < 2));
      up && (up.disabled = index === 0);
      down && (down.disabled = index === items.length - 1);
    });
  }
}
