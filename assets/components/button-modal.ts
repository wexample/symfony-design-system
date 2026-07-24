import ModalService from '@wexample/symfony-loader/js/Services/ModalService';
import AbstractOverlayButton from '../js/Class/AbstractOverlayButton';

export const HASH_KEY_MODAL = 'modal';
export const HASH_KEY_MODAL_OPTS = 'modal.opts';
export const HASH_KEY_MODAL_PAGE = 'modal.page';

export default class extends AbstractOverlayButton {
  protected getHashKeys(): [string, string, string] { return [HASH_KEY_MODAL, HASH_KEY_MODAL_OPTS, HASH_KEY_MODAL_PAGE]; }
  protected getOptionsAttribute(): string { return 'data-modal-options'; }
  protected openOverlay(href: string, options: Record<string, any>): void {
    (this.app.getService(ModalService) as ModalService).get(href, options);
  }
}
