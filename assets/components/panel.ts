import AbstractOverlayPageManager from '../js/Class/AbstractOverlayPageManager';
import { HASH_KEY_PANEL, HASH_KEY_PANEL_OPTS, HASH_KEY_PANEL_PAGE } from './button-panel';

export default class extends AbstractOverlayPageManager {
  public overlayDepthGroup = 'panel';
  protected getContentSelector() { return '.panel--content'; }
  protected getCloseLinkSelector() { return '.panel-close a'; }
  protected getHashKeys(): [string, string, string] { return [HASH_KEY_PANEL, HASH_KEY_PANEL_OPTS, HASH_KEY_PANEL_PAGE]; }
}
