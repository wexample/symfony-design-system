import AbstractOverlayPageManager from '../../js/Class/AbstractOverlayPageManager';
import { TARGET_HASH_KEYS, TARGET_MODAL } from '../../js/Helper/TargetHelper';

export default class extends AbstractOverlayPageManager {
  public overlayDepthGroup = 'modal';
  protected getContentSelector() { return '.modal--content'; }
  protected getCloseLinkSelector() { return '.modal-close a'; }
  protected getHashKeys(): [string, string, string] { return TARGET_HASH_KEYS[TARGET_MODAL]; }
  protected get useScopedMainClass() { return true; }
}
