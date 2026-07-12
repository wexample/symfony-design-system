import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private toggleEl?: HTMLButtonElement;

  protected async activateListeners(): Promise<void> {
    this.toggleEl = this.el.querySelector(
      '.menu-item-collapsible--toggle'
    ) as HTMLButtonElement;

    this.toggleEl?.addEventListener('click', this.onToggleClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.toggleEl?.removeEventListener('click', this.onToggleClick);
  }

  private onToggleClick = () => {
    this.el.classList.toggle('is-open');
  };
}
