import Component from '@wexample/symfony-loader/js/Class/Component';
import KeyboardService from '@wexample/symfony-loader/js/Services/KeyboardService';
import { focusTrapCanReturn, focusTrapFocusables, focusTrapNext } from '../../js/Helper/FocusTrapHelper';

// A side menu of the layout, folded and unfolded by its two toggles — the one
// in its own bar, the one the header keeps for it. Wide, folding is a choice
// the reader keeps (`ui.layout.menu.<id>`). In a window the stylesheet makes
// it a drawer (`--menu-drawer`), the toggles slide it over the page instead,
// for the moment only: it holds the keyboard as a dialog does, and Escape, a
// press outside or a link followed close it.
export default class extends Component {
  private menuId?: string;
  private closeEl?: HTMLElement;
  private openEl?: HTMLElement;
  private returnFocusEl: HTMLElement | null = null;

  protected async activateListeners(): Promise<void> {
    this.menuId = this.el.dataset.menuId;
    this.closeEl = this.el.querySelector('.menu--toggle-close') as HTMLElement;
    this.openEl = this.menuId
      ? (document.querySelector(`[data-menu-target="${this.menuId}"]`) as HTMLElement)
      : undefined;

    this.closeEl?.addEventListener('click', this.onClose);
    this.openEl?.addEventListener('click', this.onOpen);
    this.el.addEventListener('click', this.onClickDrawer);
    window.addEventListener('resize', this.onResize);

    const keyboard = this.app.getServiceOrFail(KeyboardService) as KeyboardService;
    const whileOpen = { priority: 90, enabled: () => this.isDrawerOpen() };
    keyboard.registerKeyDown(this, KeyboardService.KEY_ESCAPE, this.onEscape, whileOpen);
    keyboard.registerKeyDown(this, KeyboardService.KEY_TAB, this.onTab, whileOpen);

    const isCollapsed = this.el.classList.contains('gutters--collapsible--collapsed');
    if (this.openEl) {
      this.openEl.hidden = !isCollapsed;
    }
    this.syncExpanded();
  }

  protected async deactivateListeners(): Promise<void> {
    this.closeEl?.removeEventListener('click', this.onClose);
    this.openEl?.removeEventListener('click', this.onOpen);
    this.el.removeEventListener('click', this.onClickDrawer);
    window.removeEventListener('resize', this.onResize);
    (this.app.getServiceOrFail(KeyboardService) as KeyboardService).unregisterOwner(this);
  }

  // What the stylesheet decided for this window: the menu a drawer or not.
  private isDrawer(): boolean {
    return getComputedStyle(this.el).getPropertyValue('--menu-drawer').trim() === '1';
  }

  private isDrawerOpen(): boolean {
    return this.el.classList.contains('is-drawer-open');
  }

  // Whether the reader sees the menu, said by both toggles.
  private syncExpanded(): void {
    const expanded = this.isDrawer()
      ? this.isDrawerOpen()
      : !this.el.classList.contains('gutters--collapsible--collapsed');

    this.openEl?.setAttribute('aria-expanded', String(expanded));
    this.closeEl?.setAttribute('aria-expanded', String(expanded));
  }

  private openDrawer(): void {
    const active = document.activeElement as HTMLElement | null;
    this.returnFocusEl = active && !this.el.contains(active) && focusTrapCanReturn(active) ? active : null;

    this.el.classList.add('is-drawer-open');
    this.el.setAttribute('role', 'dialog');
    this.el.setAttribute('aria-modal', 'true');
    this.syncExpanded();

    // Once it shows: what is not shown cannot take the focus.
    requestAnimationFrame(() => {
      const menuEl = this.el.querySelector<HTMLElement>('.menu') ?? this.el;
      (focusTrapFocusables(menuEl)[0] ?? menuEl).focus({ preventScroll: true });
    });
  }

  private closeDrawer(): void {
    if (!this.isDrawerOpen()) {
      return;
    }

    this.el.classList.remove('is-drawer-open');
    this.el.removeAttribute('role');
    this.el.removeAttribute('aria-modal');
    this.syncExpanded();

    if (focusTrapCanReturn(this.returnFocusEl)) {
      this.returnFocusEl.focus({ preventScroll: true });
    }
    this.returnFocusEl = null;
  }

  private onClose = (e: Event): void => {
    e.preventDefault();

    if (this.isDrawer()) {
      this.closeDrawer();
      return;
    }

    this.el.classList.add('gutters--collapsible--collapsed');
    if (this.openEl) {
      this.openEl.hidden = false;
    }
    this.syncExpanded();
    this.app.onMenuStateChange(this.menuId, false);
  };

  private onOpen = (e: Event): void => {
    e.preventDefault();

    if (this.isDrawer()) {
      this.openDrawer();
      return;
    }

    this.el.classList.remove('gutters--collapsible--collapsed');
    if (this.openEl) {
      this.openEl.hidden = true;
    }
    this.syncExpanded();
    this.app.onMenuStateChange(this.menuId, true);
  };

  // A press on the dimmed page closes; a link followed in the menu too — the
  // page it leads to is what the reader asked for, not the menu again.
  private onClickDrawer = (e: MouseEvent): void => {
    if (!this.isDrawerOpen()) {
      return;
    }

    const target = e.target as HTMLElement;
    const link = target.closest<HTMLAnchorElement>('a[href]');

    if (target === this.el || (link && link.getAttribute('href') !== '#' && !link.classList.contains('menu--toggle-close'))) {
      this.closeDrawer();
    }
  };

  private onEscape = (): boolean => {
    this.closeDrawer();

    return true;
  };

  private onTab = (event: KeyboardEvent): boolean => {
    const menuEl = this.el.querySelector<HTMLElement>('.menu') ?? this.el;
    const focusables = focusTrapFocusables(menuEl);
    const active = document.activeElement as HTMLElement | null;
    const next = focusables.length
      ? focusTrapNext(focusables, active && focusables.includes(active) ? active : null, event.shiftKey)
      : menuEl;

    if (!next) {
      return false;
    }

    event.preventDefault();
    next.focus();

    return true;
  };

  // Widened past the drawer's windows while open: the menu goes back to its
  // place in the row, as its reader left it there.
  private onResize = (): void => {
    if (this.isDrawerOpen() && !this.isDrawer()) {
      this.closeDrawer();
    }

    this.syncExpanded();
  };
}
