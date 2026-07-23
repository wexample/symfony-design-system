import Component from '@wexample/symfony-loader/js/Class/Component';
import AssetUsage from '@wexample/symfony-loader/js/Class/AssetUsage';

export default class extends Component {
  private checked: boolean = false;

  private onToggle = async () => {
    this.checked = !this.checked;
    this.el.setAttribute('data-checked', String(this.checked));
    const scheme = this.checked ? 'light' : 'dark';
    await (this.app.layout as any).setUsage(AssetUsage.USAGE_COLOR_SCHEME, scheme, true);
  };

  protected async activateListeners(): Promise<void> {
    this.checked = this.app.layout.el.classList.contains('usage-color-scheme-light');
    this.el.setAttribute('data-checked', String(this.checked));
    this.el.addEventListener('click', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onToggle);
  }
}
