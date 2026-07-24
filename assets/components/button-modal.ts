import Component from '@wexample/symfony-loader/js/Class/Component';
import ModalService from '@wexample/symfony-loader/js/Services/ModalService';
import { locationHashParamGet, locationHashParamSet } from '@wexample/js-helpers/Helper/Location';

export const HASH_KEY_MODAL = 'modal';
export const HASH_KEY_MODAL_OPTS = 'modal.opts';
export const HASH_KEY_MODAL_PAGE = 'modal.page';

export default class extends Component {
  private linkEl?: HTMLAnchorElement;

  protected async mounted(): Promise<void> {
    this.linkEl = this.el as HTMLAnchorElement;
    this.linkEl.addEventListener('click', this.onClick);
    await super.mounted();

    const savedUrl = locationHashParamGet(HASH_KEY_MODAL);
    const savedPage = locationHashParamGet(HASH_KEY_MODAL_PAGE);
    const href = this.linkEl?.getAttribute('href');

    if (savedUrl && href && savedUrl === href && savedPage === window.location.pathname) {
      const savedOptsRaw = locationHashParamGet(HASH_KEY_MODAL_OPTS);
      const opts = savedOptsRaw ? JSON.parse(savedOptsRaw) : {};
      this.openModal(href, opts);
    }
  }

  protected async unmounted(): Promise<void> {
    this.linkEl?.removeEventListener('click', this.onClick);
    await super.unmounted();
  }

  private openModal(href: string, options: Record<string, any>): void {
    locationHashParamSet(HASH_KEY_MODAL, href, true);
    locationHashParamSet(HASH_KEY_MODAL_PAGE, window.location.pathname, true);
    if (Object.keys(options).length) {
      locationHashParamSet(HASH_KEY_MODAL_OPTS, JSON.stringify(options), true);
    }
    (this.app.getService(ModalService) as ModalService).get(href, options);
  }

  private onClick = (event: Event) => {
    const href = this.linkEl?.getAttribute('href');
    if (!href || href === '#') {
      return;
    }
    event.preventDefault();
    const optionsRaw = this.linkEl?.getAttribute('data-modal-options');
    const options = optionsRaw ? JSON.parse(optionsRaw) : {};
    this.openModal(href, options);
  };
}
