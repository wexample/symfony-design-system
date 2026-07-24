import AbstractOverlayPageManager from '../js/Class/AbstractOverlayPageManager';
import { HASH_KEY_MODAL, HASH_KEY_MODAL_OPTS, HASH_KEY_MODAL_PAGE } from './button-modal';

export default class extends AbstractOverlayPageManager {
  protected getContentSelector() { return '.modal--content'; }
  protected getCloseLinkSelector() { return '.modal-close a'; }
  protected getHashKeys(): [string, string, string] { return [HASH_KEY_MODAL, HASH_KEY_MODAL_OPTS, HASH_KEY_MODAL_PAGE]; }
  protected get useScopedMainClass() { return true; }
}
