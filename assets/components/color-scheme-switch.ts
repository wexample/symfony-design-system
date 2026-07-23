import Component from '@wexample/symfony-loader/js/Class/Component';
import AssetUsage from '@wexample/symfony-loader/js/Class/AssetUsage';

export default class extends Component {
  private scheme: string = 'light';

  private onToggle = async () => {
    this.scheme = this.scheme === 'dark' ? 'light' : 'dark';
    this.el.setAttribute('data-scheme', this.scheme);
    await (this.app.layout as any).setUsage(AssetUsage.USAGE_COLOR_SCHEME, this.scheme, true);
  };

  protected async activateListeners(): Promise<void> {
    const layoutEl = this.app.layout.el;
    this.scheme = layoutEl.classList.contains('usage-color-scheme-dark') ? 'dark' : 'light';
    this.el.setAttribute('data-scheme', this.scheme);
    this.el.addEventListener('click', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onToggle);
  }
}
