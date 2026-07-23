import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private closeEl?: HTMLElement;
  private openEl?: HTMLElement;

  protected async activateListeners(): Promise<void> {
    const menuId = this.el.dataset.menuId;
    this.closeEl = this.el.querySelector('.menu--toggle-close') as HTMLElement;
    this.openEl = menuId
      ? (document.querySelector(`[data-menu-target="${menuId}"]`) as HTMLElement)
      : undefined;

    this.closeEl?.addEventListener('click', this.onClose);
    this.openEl?.addEventListener('click', this.onOpen);
  }

  protected async deactivateListeners(): Promise<void> {
    this.closeEl?.removeEventListener('click', this.onClose);
    this.openEl?.removeEventListener('click', this.onOpen);
  }

  private onClose = (e: Event): void => {
    e.preventDefault();
    this.el.classList.add('gutters--collapsible--collapsed');
  };

  private onOpen = (e: Event): void => {
    e.preventDefault();
    this.el.classList.remove('gutters--collapsible--collapsed');
  };
}
