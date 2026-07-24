import Component from '@wexample/symfony-loader/js/Class/Component';
import { locationHashParamGet, locationHashParamSet } from '@wexample/js-helpers/Helper/Location';

export default abstract class AbstractOverlayButton extends Component {
  private linkEl?: HTMLAnchorElement;

  protected abstract getHashKeys(): [string, string, string];
  protected abstract getOptionsAttribute(): string;
  protected abstract openOverlay(href: string, options: Record<string, any>): void;

  protected async mounted(): Promise<void> {
    this.linkEl = this.el as HTMLAnchorElement;
    this.linkEl.addEventListener('click', this.onClick);
    await super.mounted();

    const [hashKey, hashOptsKey, hashPageKey] = this.getHashKeys();
    const savedUrl = locationHashParamGet(hashKey);
    const savedPage = locationHashParamGet(hashPageKey);
    const href = this.linkEl?.getAttribute('href');

    if (savedUrl && href && savedUrl === href && savedPage === window.location.pathname) {
      const savedOptsRaw = locationHashParamGet(hashOptsKey);
      const opts = savedOptsRaw ? JSON.parse(savedOptsRaw) : {};
      this.openWithPersistence(href, opts);
    }
  }

  protected async unmounted(): Promise<void> {
    this.linkEl?.removeEventListener('click', this.onClick);
    await super.unmounted();
  }

  private openWithPersistence(href: string, options: Record<string, any>): void {
    const [hashKey, hashOptsKey, hashPageKey] = this.getHashKeys();
    locationHashParamSet(hashKey, href, true);
    locationHashParamSet(hashPageKey, window.location.pathname, true);
    if (Object.keys(options).length) {
      locationHashParamSet(hashOptsKey, JSON.stringify(options), true);
    }
    this.openOverlay(href, options);
  }

  private onClick = (event: Event) => {
    const href = this.linkEl?.getAttribute('href');
    if (!href || href === '#') {
      return;
    }
    event.preventDefault();
    const optionsRaw = this.linkEl?.getAttribute(this.getOptionsAttribute());
    const options = optionsRaw ? JSON.parse(optionsRaw) : {};
    this.openWithPersistence(href, options);
  };
}
