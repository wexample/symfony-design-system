import Component from '@wexample/symfony-loader/js/Class/Component';
import { locationHashParamGet, locationHashParamSet } from '@wexample/js-helpers/Helper/Location';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import { rippleAgentPress, rippleOnPress } from '../../js/Helper/RippleHelper';
import { loadIntoTarget, targetEmbeddedOf, TARGET_HASH_KEYS } from '../../js/Helper/TargetHelper';

// A button opening what it leads to in a target — a panel, a modal — rather
// than in the page. To an agent, a button it may press (`button-target.ai.yml`).
export default class extends Component {
  private linkEl?: HTMLAnchorElement;

  private stopRipple: () => void = () => {};

  protected async mounted(): Promise<void> {
    this.linkEl = this.el as HTMLAnchorElement;
    this.linkEl.addEventListener('click', this.onClick);
    this.stopRipple = rippleOnPress(this.linkEl);
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
    this.stopRipple();
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

    loadIntoTarget(this.app, this.getTarget(), href, {
      ...options,
      targetEmbedded: targetEmbeddedOf(this.linkEl ?? null),
    });
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

  public getAgentLabel(): string | undefined {
    return this.el.getAttribute('aria-label')
      || this.el.querySelector(':scope > span:last-child')?.textContent?.trim()
      || this.el.getAttribute('title')
      || undefined;
  }

  public getAgentState(): Record<string, any> {
    return { opens: this.getTarget() || null };
  }

  public getAgentActions(): AgentActionsImplementation {
    return {
      // Pressed where the reader sees it, then opened as a click opens it.
      click: async () => {
        await rippleAgentPress(this.app, this.linkEl);
        this.linkEl?.click();

        return { clicked: this.getAgentLabel(), opened: this.getTarget() || null };
      },
    };
  }
}
