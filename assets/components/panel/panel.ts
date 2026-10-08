import AbstractOverlayPageManager from '../../js/Class/AbstractOverlayPageManager';
import { OverlayResizeOptions } from '../../js/Helper/OverlayResizeHelper';
import { TARGET_HASH_KEYS, TARGET_PANEL } from '../../js/Helper/TargetHelper';

export default class extends AbstractOverlayPageManager {
  public overlayDepthGroup = 'panel';
  protected getContentSelector() { return '.panel--content'; }
  protected getCloseLinkSelector() { return '.panel-close a'; }
  protected getHashKeys(): [string, string, string] { return TARGET_HASH_KEYS[TARGET_PANEL]; }
  protected getLayoutBase() { return 'panel'; }

  // Held against the window's end: only the edge facing the page pulls — its
  // start, which is on the left of a page read from the left and on the right
  // of one read from the right, the panel following the reading.
  protected getResizeOptions(): Omit<OverlayResizeOptions, 'stateKey'> {
    return { edges: ['start'], centered: false };
  }
}
