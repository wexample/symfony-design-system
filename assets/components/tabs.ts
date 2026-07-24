import Component from '@wexample/symfony-loader/js/Class/Component';
import { locationHashParamGet, locationHashParamSet } from '@wexample/js-helpers/Helper/Location';

export default class extends Component {
  private group: string;
  private hashKey: string;
  private onHashChangeProxy?: EventListener;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.group = (this.options?.group as string) || 'default';
    this.hashKey = this.resolveHashKey();

    this.el.querySelectorAll<HTMLElement>('.tabs--item[data-tab]').forEach(el => {
      el.addEventListener('click', this.onTabClick);
    });

    this.onHashChangeProxy = this.onHashChange.bind(this) as EventListener;
    window.addEventListener('hashchange', this.onHashChangeProxy);

    const stored = locationHashParamGet(this.hashKey);
    if (stored) {
      this.activate(stored, true);
    } else {
      const firstTab = this.el.querySelector<HTMLElement>('.tabs--item[data-tab]');
      if (firstTab) {
        this.activate(firstTab.getAttribute('data-tab')!, true);
      }
    }
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.querySelectorAll<HTMLElement>('.tabs--item[data-tab]').forEach(el => {
      el.removeEventListener('click', this.onTabClick);
    });

    if (this.onHashChangeProxy) {
      window.removeEventListener('hashchange', this.onHashChangeProxy);
    }

    await super.deactivateListeners();
  }

  private resolveHashKey(): string {
    // Auto-scope: tabs inside a modal use 'tab.modal.{group}', inside a panel 'tab.panel.{group}'
    // This prevents conflicts when the same group name is used in both the page and an overlay.
    if (this.el.closest('.modal--content')) {
      return `tab.modal.${this.group}`;
    }
    if (this.el.closest('.panel--content')) {
      return `tab.panel.${this.group}`;
    }
    return `tab.${this.group}`;
  }

  private onTabClick = (event: Event) => {
    event.preventDefault();
    const tab = (event.currentTarget as HTMLElement).getAttribute('data-tab');
    if (tab) this.activate(tab);
  };

  private onHashChange = () => {
    const stored = locationHashParamGet(this.hashKey);
    if (stored) this.activate(stored, true);
  };

  activate(tabName: string, ignoreHistory = false): void {
    locationHashParamSet(this.hashKey, tabName, ignoreHistory);

    this.el.querySelectorAll<HTMLElement>('.tabs--item[data-tab]').forEach(el => {
      el.classList.toggle('tabs--item--active', el.getAttribute('data-tab') === tabName);
    });

    const panels = this.el.parentElement?.querySelector<HTMLElement>('.tabs-panels');
    if (panels) {
      panels.querySelectorAll<HTMLElement>('[data-tab]').forEach(panel => {
        panel.hidden = panel.getAttribute('data-tab') !== tabName;
      });
    }
  }
}
