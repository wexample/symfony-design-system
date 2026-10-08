import AbstractOverlayPageManager from '../../js/Class/AbstractOverlayPageManager';
import { OverlayResizeOptions } from '../../js/Helper/OverlayResizeHelper';
import { TARGET_HASH_KEYS, TARGET_MODAL } from '../../js/Helper/TargetHelper';

export default class extends AbstractOverlayPageManager {
  public overlayDepthGroup = 'modal';
  protected getContentSelector() { return '.modal--content'; }
  protected getCloseLinkSelector() { return '.modal-close a'; }
  protected getHashKeys(): [string, string, string] { return TARGET_HASH_KEYS[TARGET_MODAL]; }
  protected get useScopedMainClass() { return true; }
  protected getLayoutBase() { return 'modal'; }

  // In the middle of the window: every edge pulls, and the box grows on both
  // sides of the one pulled.
  protected getResizeOptions(): Omit<OverlayResizeOptions, 'stateKey'> {
    return { edges: ['start', 'end', 'top', 'bottom'], centered: true };
  }
}
