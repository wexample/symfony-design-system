import AbstractOverlayPageManager from '../js/Class/AbstractOverlayPageManager';
import { TARGET_HASH_KEYS, TARGET_PANEL } from '../js/Helper/TargetHelper';

export default class extends AbstractOverlayPageManager {
  public overlayDepthGroup = 'panel';
  protected getContentSelector() { return '.panel--content'; }
  protected getCloseLinkSelector() { return '.panel-close a'; }
  protected getHashKeys(): [string, string, string] { return TARGET_HASH_KEYS[TARGET_PANEL]; }
}
