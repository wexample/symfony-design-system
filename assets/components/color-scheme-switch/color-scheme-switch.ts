import AssetUsage from '@wexample/symfony-loader/js/Class/AssetUsage';
import Switch from '../switch/switch';

// A switch whose two states are the colour schemes, light when on: switching
// sets the layout's scheme and keeps the choice beyond the page.
export default class extends Switch {
  protected async changed(checked: boolean): Promise<void> {
    const scheme = checked ? 'light' : 'dark';

    await (this.app.layout as any).setUsage(AssetUsage.USAGE_COLOR_SCHEME, scheme, true);
    this.app.persistUiState('ui.color_scheme', scheme);
  }

  // The scheme the page shows, whatever the markup was drawn with.
  protected async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.setChecked(!document.body.classList.contains('usage-color-scheme-dark'));
  }
}
