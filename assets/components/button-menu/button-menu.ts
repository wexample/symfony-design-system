import Component from '@wexample/symfony-loader/js/Class/Component';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';

export default class extends Component {
  protected overlayUseBackdrop: boolean = false;
  protected overlayUseStack: boolean = false;
  protected overlaySetHiddenOnOpen: boolean = false;
  protected overlaySetHiddenOnClose: boolean = false;
  protected overlayExitOnClose: boolean = false;
  private buttonEl?: HTMLButtonElement;
  private panelEl?: HTMLElement;
  private itemLinks: HTMLElement[] = [];
  private submenuRows: HTMLElement[] = [];
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

    if (el.classList.contains('button-menu--toggle')) {
      event.preventDefault();
      this.toggleItem(el as HTMLButtonElement);

      return;
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

  // A branch opens to the right of the panel unless the window ends there.
  private placeSubmenu(el: HTMLElement): void {
    const list = el.parentElement?.querySelector(
      '.button-menu--sublist'
    ) as HTMLElement | null;

    if (!list) {
      return;
    }

    list.classList.remove('button-menu--sublist--left');

    if (list.getBoundingClientRect().right > window.innerWidth) {
      list.classList.add('button-menu--sublist--left');
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

    this.defaultAlign = this.panelEl.classList.contains('button-menu--panel--right')
      ? 'right'
      : 'left';
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

      const buttonRect = this.buttonEl.getBoundingClientRect();
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
    });
  }

  private resetPlacement(): void {
    this.applyPlacement(this.defaultAlign, this.defaultVertical);
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
