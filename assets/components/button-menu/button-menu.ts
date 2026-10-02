import Component from '@wexample/symfony-loader/js/Class/Component';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import { filterTextMatches } from '../../js/Helper/FilterHelper';
import { loadIntoTarget } from '../../js/Helper/TargetHelper';

export default class ButtonMenu extends Component {
  // How close to its bar's end a button stands to be at the end of it: the
  // bar's own padding and the item's, not a neighbour's width.
  private static readonly BAR_END_DISTANCE = 32;

  protected overlayUseBackdrop: boolean = false;
  protected overlayUseStack: boolean = false;
  protected overlaySetHiddenOnOpen: boolean = false;
  protected overlaySetHiddenOnClose: boolean = false;
  protected overlayExitOnClose: boolean = false;
  private buttonEl?: HTMLButtonElement;
  private panelEl?: HTMLElement;
  private itemLinks: HTMLElement[] = [];
  private submenuRows: HTMLElement[] = [];
  private filterInput?: HTMLInputElement;
  // What the field narrows the list to, as the visitor types.
  private onFilterInput = () => this.applyFilter();
  // Enter in the field follows the first item left.
  private onFilterKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
      event.preventDefault();
      this.visibleRows()[0]?.querySelector<HTMLElement>('a, button')?.click();
    }
  };
  private defaultAlign: 'left' | 'right' = 'left';
  private defaultVertical: 'bottom' | 'top' = 'bottom';
  private onDocumentMouseDown = (event: MouseEvent) => {
    const target = event.target as Node | null;
    if (!target || !this.el) {
      return;
    }

    if (this.el.contains(target)) {
      return;
    }

    (this as any).overlayClose();
  };

  async init() {
    OverlayMixin.apply(this);
    await super.init();
  }

  private onSubmenuEnter = (event: Event) => {
    this.placeSubmenu(event.currentTarget as HTMLElement);
  };

  private onButtonClick = (event: Event) => {
    event.preventDefault();
    (this as any).overlayToggle(event);
  };

  // A link chose something and the menu has done its job. A toggle and a
  // branch have not: the panel stays open under them, since a menu of filters
  // is read by the handful, not one at a time.
  private onItemClick = (event: Event) => {
    const el = event.currentTarget as HTMLElement;

    if (el.classList.contains('button-menu--submenu')) {
      event.preventDefault();
      el.parentElement?.classList.toggle('is-open');
      this.placeSubmenu(el);

      return;
    }

    // A toggle that is a link keeps its state in the address it leads to:
    // followed, not flipped.
    if (el.classList.contains('button-menu--toggle') && el.tagName !== 'A') {
      event.preventDefault();
      this.toggleItem(el as HTMLButtonElement);

      return;
    }

    // A link with a target opens its page there — a panel, a modal — as a
    // target button does. A modified click still opens a tab.
    const mouse = event as MouseEvent;
    const target = el.getAttribute('data-target');
    const href = el.getAttribute('href');

    if (target && href && href !== '#' && !(mouse.ctrlKey || mouse.metaKey || mouse.shiftKey || mouse.altKey)) {
      event.preventDefault();
      loadIntoTarget(this.app, target, href, {});
    }

    (this as any).overlayClose();
  };

  // The state is flipped here and said out loud: the page that placed the menu
  // listens for it rather than reading classes off the panel.
  private toggleItem(el: HTMLButtonElement): void {
    const checked = el.getAttribute('aria-checked') !== 'true';

    el.setAttribute('aria-checked', checked ? 'true' : 'false');
    el.classList.toggle('is-checked', checked);

    el.dispatchEvent(
      new CustomEvent('button-menu:toggle', {
        bubbles: true,
        detail: {
          value: el.dataset.value ?? null,
          checked,
        },
      })
    );
  }

  // A branch opens toward the end of the line — the right, or the left in a
  // page read from the right — unless the window ends there.
  private placeSubmenu(el: HTMLElement): void {
    const list = el.parentElement?.querySelector(
      '.button-menu--sublist'
    ) as HTMLElement | null;

    if (!list) {
      return;
    }

    const rtl = this.isRtl();
    list.classList.toggle('button-menu--sublist--left', rtl);

    const rect = list.getBoundingClientRect();

    if (!rtl && rect.right > window.innerWidth) {
      list.classList.add('button-menu--sublist--left');
    } else if (rtl && rect.left < 0) {
      list.classList.remove('button-menu--sublist--left');
    }
  }

  protected async activateListeners(): Promise<void> {
    this.buttonEl = this.el.querySelector('.button--menu') as HTMLButtonElement;
    this.panelEl = this.el.querySelector('.button-menu--panel') as HTMLElement;
    this.itemLinks = Array.from(
      this.el.querySelectorAll('.button-menu--link')
    ) as HTMLElement[];
    this.submenuRows = Array.from(
      this.el.querySelectorAll('.button-menu--submenu')
    ) as HTMLElement[];

    if (!this.buttonEl || !this.panelEl) {
      throw new Error('Button menu elements not found.');
    }

    // The alignment asked for is along the line: its start is the left, or
    // the right in a page read from the right. The placement itself is
    // measured in screen coordinates, so it is turned here once.
    const alignEnd = this.panelEl.classList.contains('button-menu--panel--right');
    this.defaultAlign = alignEnd !== this.isRtl() ? 'right' : 'left';
    this.defaultVertical = this.panelEl.classList.contains('button-menu--panel--top')
      ? 'top'
      : 'bottom';

    this.buttonEl.addEventListener('click', this.onButtonClick);

    this.itemLinks.forEach((link) => {
      link.addEventListener('click', this.onItemClick);
    });

    this.submenuRows.forEach((row) => {
      row.addEventListener('mouseenter', this.onSubmenuEnter);
    });

    this.filterInput = this.el.querySelector<HTMLInputElement>('.button-menu--filter-input') ?? undefined;
    this.filterInput?.addEventListener('input', this.onFilterInput);
    this.filterInput?.addEventListener('keydown', this.onFilterKeyDown);
  }

  protected async deactivateListeners(): Promise<void> {
    if (this.buttonEl) {
      this.buttonEl.removeEventListener('click', this.onButtonClick);
    }

    this.itemLinks.forEach((link) => {
      link.removeEventListener('click', this.onItemClick);
    });

    this.submenuRows.forEach((row) => {
      row.removeEventListener('mouseenter', this.onSubmenuEnter);
    });

    this.filterInput?.removeEventListener('input', this.onFilterInput);
    this.filterInput?.removeEventListener('keydown', this.onFilterKeyDown);
  }

  // The rows of the list the field has left, separators apart.
  private visibleRows(): HTMLElement[] {
    return Array.from(this.panelEl?.querySelectorAll<HTMLElement>(':scope > .button-menu--list > li:not(.button-menu--separator)') ?? [])
      .filter((row) => !row.hidden);
  }

  private applyFilter(): void {
    const query = this.filterInput?.value ?? '';
    const rows = Array.from(this.panelEl?.querySelectorAll<HTMLElement>(':scope > .button-menu--list > li') ?? []);

    rows.forEach((row) => {
      if (row.classList.contains('button-menu--separator')) {
        // A heading means nothing once its run is cut into.
        row.hidden = query.trim() !== '';

        return;
      }

      const target = row.querySelector<HTMLElement>('a, button');
      row.hidden = !filterTextMatches(
        query,
        row.querySelector('.button-menu--item-label')?.textContent,
        target?.dataset.filter,
      );
    });

    const empty = this.panelEl?.querySelector<HTMLElement>('.button-menu--filter-empty');

    if (empty) {
      empty.hidden = this.visibleRows().length > 0;
    }
  }

  overlayOnOpen(): void {
    if (this.buttonEl) {
      this.buttonEl.setAttribute('aria-expanded', 'true');
    }
    if (this.panelEl) {
      this.panelEl.removeAttribute('hidden');
    }

    document.addEventListener('mousedown', this.onDocumentMouseDown);
    this.updatePlacement();

    // A long menu opens on its field: the visitor types before they scroll.
    if (this.filterInput) {
      this.filterInput.value = '';
      this.applyFilter();
      requestAnimationFrame(() => this.filterInput?.focus());
    }
  }

  overlayOnClose(): void {
    if (this.buttonEl) {
      this.buttonEl.setAttribute('aria-expanded', 'false');
    }
    if (this.panelEl) {
      this.panelEl.setAttribute('hidden', 'hidden');
    }

    document.removeEventListener('mousedown', this.onDocumentMouseDown);
    this.submenuRows.forEach((row) =>
      row.parentElement?.classList.remove('is-open')
    );
    this.resetPlacement();
  }

  overlayOnEscape(): void {
    (this as any).overlayClose();
    this.buttonEl?.focus();
  }

  private updatePlacement(): void {
    if (!this.buttonEl || !this.panelEl) {
      return;
    }

    requestAnimationFrame(() => {
      if (!this.buttonEl || !this.panelEl) {
        return;
      }

      // A split button's panel lines up with the whole of it, not its caret.
      const buttonRect = (this.el.querySelector('.button-menu--split-group') ?? this.buttonEl).getBoundingClientRect();
      const panelRect = this.panelEl.getBoundingClientRect();
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;

      const leftCandidate = this.getHorizontalCandidate(buttonRect, panelRect, viewportWidth, 'left');
      const rightCandidate = this.getHorizontalCandidate(buttonRect, panelRect, viewportWidth, 'right');
      const topCandidate = this.getVerticalCandidate(buttonRect, panelRect, viewportHeight, 'top');
      const bottomCandidate = this.getVerticalCandidate(buttonRect, panelRect, viewportHeight, 'bottom');

      let align = this.defaultAlign;
      if (leftCandidate.fits && !rightCandidate.fits) {
        align = 'left';
      } else if (rightCandidate.fits && !leftCandidate.fits) {
        align = 'right';
      } else if (leftCandidate.fits && rightCandidate.fits) {
        align = this.defaultAlign;
      } else {
        align = this.defaultAlign;
      }

      let vertical: 'top' | 'bottom' = this.defaultVertical;
      if (topCandidate.fits && !bottomCandidate.fits) {
        vertical = 'top';
      } else if (bottomCandidate.fits && !topCandidate.fits) {
        vertical = 'bottom';
      } else {
        vertical = this.defaultVertical;
      }

      this.applyPlacement(align, vertical);
      this.alignOnBar(buttonRect, align, vertical);
    });
  }

  // A menu opened from a bar — the header, a toolbar — starts where the bar
  // ends, not where its button does, whatever the bar's thickness. And from a
  // button standing at the bar's end, it lines up with the bar's edge rather
  // than the button's: the header's edge being the window's, the last menu of
  // the header hugs the window, while a toolbar inside the page keeps it to
  // the toolbar's own edge.
  private alignOnBar(
    buttonRect: DOMRect,
    align: 'left' | 'right',
    vertical: 'top' | 'bottom'
  ): void {
    if (!this.panelEl) {
      return;
    }

    const panelStyle = this.panelEl.style;
    panelStyle.marginTop = '';
    panelStyle.marginBottom = '';
    panelStyle.left = '';
    panelStyle.right = '';

    const bar = this.el.closest<HTMLElement>('.header, .toolbar');

    if (!bar) {
      return;
    }

    const barRect = bar.getBoundingClientRect();

    if (vertical === 'bottom') {
      panelStyle.marginTop = `${Math.max(0, barRect.bottom - buttonRect.bottom)}px`;
    } else {
      panelStyle.marginBottom = `${Math.max(0, buttonRect.top - barRect.top)}px`;
    }

    const toEnd = align === 'right' ? barRect.right - buttonRect.right : buttonRect.left - barRect.left;

    if (toEnd > 0 && toEnd <= ButtonMenu.BAR_END_DISTANCE) {
      panelStyle[align] = `${-toEnd}px`;
    }
  }

  private isRtl(): boolean {
    return getComputedStyle(this.el).direction === 'rtl';
  }

  private resetPlacement(): void {
    this.applyPlacement(this.defaultAlign, this.defaultVertical);

    if (this.panelEl) {
      Object.assign(this.panelEl.style, { marginTop: '', marginBottom: '', left: '', right: '' });
    }
  }

  private applyPlacement(
    horizontal: 'left' | 'right',
    vertical: 'top' | 'bottom'
  ): void {
    if (!this.panelEl) {
      return;
    }

    this.panelEl.classList.remove(
      'button-menu--panel--left',
      'button-menu--panel--right',
      'button-menu--panel--top'
    );

    this.panelEl.classList.add(`button-menu--panel--${horizontal}`);

    if (vertical === 'top') {
      this.panelEl.classList.add('button-menu--panel--top');
    }
  }

  private getHorizontalCandidate(
    buttonRect: DOMRect,
    panelRect: DOMRect,
    viewportWidth: number,
    mode: 'left' | 'right'
  ) {
    const left = mode === 'left'
      ? buttonRect.left
      : buttonRect.right - panelRect.width;
    const right = left + panelRect.width;
    const overflow = Math.max(0, -left) + Math.max(0, right - viewportWidth);

    return {
      mode,
      fits: left >= 0 && right <= viewportWidth,
      overflow
    };
  }

  private getVerticalCandidate(
    buttonRect: DOMRect,
    panelRect: DOMRect,
    viewportHeight: number,
    mode: 'top' | 'bottom'
  ) {
    const top = mode === 'bottom'
      ? buttonRect.bottom - 1
      : buttonRect.top - panelRect.height + 1;
    const bottom = top + panelRect.height;
    const overflow = Math.max(0, -top) + Math.max(0, bottom - viewportHeight);

    return {
      mode,
      fits: top >= 0 && bottom <= viewportHeight,
      overflow
    };
  }
}
