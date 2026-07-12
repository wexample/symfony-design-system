import Component from '@wexample/symfony-loader/js/Class/Component';

export default abstract class AbstractCollapsibleComponent extends Component {
  private toggleEl?: HTMLElement;

  protected abstract getToggleSelector(): string;

  protected async activateListeners(): Promise<void> {
    this.toggleEl = this.el.querySelector(this.getToggleSelector()) as HTMLElement;
    this.toggleEl?.addEventListener('click', this.onToggleClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.toggleEl?.removeEventListener('click', this.onToggleClick);
  }

  private onToggleClick = () => {
    this.el.classList.toggle('is-open');
  };
}
