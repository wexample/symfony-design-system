import Component from '@wexample/symfony-loader/js/Class/Component';
import { locationHashParamGet, locationHashParamSet } from '@wexample/js-helpers/Helper/Location';
import { loadIntoTarget, TARGET_HASH_KEYS } from '../../js/Helper/TargetHelper';

export default class extends Component {
  private linkEl?: HTMLAnchorElement;

  protected async mounted(): Promise<void> {
    this.linkEl = this.el as HTMLAnchorElement;
    this.linkEl.addEventListener('click', this.onClick);
    await super.mounted();

    const hashKeys = this.getHashKeys();

    if (!hashKeys) {
      return;
    }

    const [hashKey, hashOptsKey, hashPageKey] = hashKeys;
    const savedUrl = locationHashParamGet(hashKey);
    const savedPage = locationHashParamGet(hashPageKey);
    const href = this.linkEl?.getAttribute('href');

    if (savedUrl && href && savedUrl === href && savedPage === window.location.pathname) {
      const savedOptsRaw = locationHashParamGet(hashOptsKey);
      const options = savedOptsRaw ? JSON.parse(savedOptsRaw) : {};

      if (options.persistent && savedUrl !== window.location.pathname) {
        this.load(href, options);
      }
    }
  }

  protected async unmounted(): Promise<void> {
    this.linkEl?.removeEventListener('click', this.onClick);
    await super.unmounted();
  }

  private getTarget(): string {
    return this.linkEl?.getAttribute('data-target') ?? '';
  }

  private getHashKeys(): [string, string, string] | undefined {
    return TARGET_HASH_KEYS[this.getTarget()];
  }

  private load(href: string, options: Record<string, any>): void {
    const hashKeys = this.getHashKeys();

    if (hashKeys && options.persistent) {
      const [hashKey, hashOptsKey, hashPageKey] = hashKeys;

      locationHashParamSet(hashKey, href, true);
      locationHashParamSet(hashPageKey, window.location.pathname, true);

      if (Object.keys(options).length) {
        locationHashParamSet(hashOptsKey, JSON.stringify(options), true);
      }
    }

    loadIntoTarget(this.app, this.getTarget(), href, options);
  }

  private onClick = (event: MouseEvent) => {
    const href = this.linkEl?.getAttribute('href');

    if (!href || href === '#') {
      return;
    }

    // A modified click stays a navigation, so the target keeps being openable as
    // a full page in a new tab.
    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
      return;
    }

    event.preventDefault();

    const optionsRaw = this.linkEl?.getAttribute('data-target-options');

    this.load(href, optionsRaw ? JSON.parse(optionsRaw) : {});
  };
}
