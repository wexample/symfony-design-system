import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private menuId?: string;
  private closeEl?: HTMLElement;
  private openEl?: HTMLElement;
  private headerMenuEl?: HTMLElement;

  protected async activateListeners(): Promise<void> {
    this.menuId = this.el.dataset.menuId;
    this.closeEl = this.el.querySelector('.menu--toggle-close') as HTMLElement;
    this.openEl = this.menuId
      ? (document.querySelector(`[data-menu-target="${this.menuId}"]`) as HTMLElement)
      : undefined;
    this.headerMenuEl = this.openEl?.closest('.header--menu') as HTMLElement ?? undefined;

    this.closeEl?.addEventListener('click', this.onClose);
    this.openEl?.addEventListener('click', this.onOpen);

    // set initial state
    const isCollapsed = this.el.classList.contains('gutters--collapsible--collapsed');
    this.headerMenuEl?.classList.toggle('is-hidden', !isCollapsed);
  }

  protected async deactivateListeners(): Promise<void> {
    this.closeEl?.removeEventListener('click', this.onClose);
    this.openEl?.removeEventListener('click', this.onOpen);
  }

  private onClose = (e: Event): void => {
    e.preventDefault();
    this.el.classList.add('gutters--collapsible--collapsed');
    this.headerMenuEl?.classList.remove('is-hidden');
    this.app.onMenuStateChange(this.menuId, false);
  };

  private onOpen = (e: Event): void => {
    e.preventDefault();
    this.el.classList.remove('gutters--collapsible--collapsed');
    this.headerMenuEl?.classList.add('is-hidden');
    this.app.onMenuStateChange(this.menuId, true);
  };
}
