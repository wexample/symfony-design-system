import Component from '@wexample/symfony-loader/js/Class/Component';
import PanelService from '@wexample/symfony-loader/js/Services/PanelService';
import { locationHashParamGet, locationHashParamSet } from '@wexample/js-helpers/Helper/Location';

export const HASH_KEY_PANEL = 'panel';
export const HASH_KEY_PANEL_OPTS = 'panel.opts';
export const HASH_KEY_PANEL_PAGE = 'panel.page';

export default class extends Component {
  private linkEl?: HTMLAnchorElement;

  protected async mounted(): Promise<void> {
    this.linkEl = this.el as HTMLAnchorElement;
    this.linkEl.addEventListener('click', this.onClick);
    await super.mounted();

    const savedUrl = locationHashParamGet(HASH_KEY_PANEL);
    const savedPage = locationHashParamGet(HASH_KEY_PANEL_PAGE);
    const href = this.linkEl?.getAttribute('href');

    if (savedUrl && href && savedUrl === href && savedPage === window.location.pathname) {
      const savedOptsRaw = locationHashParamGet(HASH_KEY_PANEL_OPTS);
      const opts = savedOptsRaw ? JSON.parse(savedOptsRaw) : {};
      this.openPanel(href, opts);
    }
  }

  protected async unmounted(): Promise<void> {
    this.linkEl?.removeEventListener('click', this.onClick);
    await super.unmounted();
  }

  private openPanel(href: string, options: Record<string, any>): void {
    locationHashParamSet(HASH_KEY_PANEL, href, true);
    locationHashParamSet(HASH_KEY_PANEL_PAGE, window.location.pathname, true);
    if (Object.keys(options).length) {
      locationHashParamSet(HASH_KEY_PANEL_OPTS, JSON.stringify(options), true);
    }
    (this.app.getService(PanelService) as PanelService).get(href, options);
  }

  private onClick = (event: Event) => {
    const href = this.linkEl?.getAttribute('href');
    if (!href || href === '#') {
      return;
    }
    event.preventDefault();
    const optionsRaw = this.linkEl?.getAttribute('data-panel-options');
    const options = optionsRaw ? JSON.parse(optionsRaw) : {};
    this.openPanel(href, options);
  };
}
