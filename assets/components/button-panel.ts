import PanelService from '@wexample/symfony-loader/js/Services/PanelService';
import AbstractOverlayButton from '../js/Class/AbstractOverlayButton';

export const HASH_KEY_PANEL = 'panel';
export const HASH_KEY_PANEL_OPTS = 'panel.opts';
export const HASH_KEY_PANEL_PAGE = 'panel.page';

export default class extends AbstractOverlayButton {
  protected getHashKeys(): [string, string, string] { return [HASH_KEY_PANEL, HASH_KEY_PANEL_OPTS, HASH_KEY_PANEL_PAGE]; }
  protected getOptionsAttribute(): string { return 'data-panel-options'; }
  protected openOverlay(href: string, options: Record<string, any>): void {
    (this.app.getService(PanelService) as PanelService).get(href, options);
  }
}
