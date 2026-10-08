import AbstractOverlayPageManager, { OverlayRequestOptionsInterface } from '../../js/Class/AbstractOverlayPageManager';
import { TARGET_HASH_KEYS, TARGET_MODAL } from '../../js/Helper/TargetHelper';
import { attachOverlayResize } from '../../js/Helper/OverlayResizeHelper';

export default class extends AbstractOverlayPageManager {
  public overlayDepthGroup = 'modal';
  protected getContentSelector() { return '.modal--content'; }
  protected getCloseLinkSelector() { return '.modal-close a'; }
  protected getHashKeys(): [string, string, string] { return TARGET_HASH_KEYS[TARGET_MODAL]; }
  protected get useScopedMainClass() { return true; }
  protected getLayoutBase() { return 'modal'; }

  private detachResize?: () => void;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.attachResize();
  }

  protected async deactivateListeners(): Promise<void> {
    this.detachResize?.();
    this.detachResize = undefined;

    await super.deactivateListeners();
  }

  // The page held here is written into the box, which takes its edges with
  // it: they are laid again over the new page.
  public setLayoutBody(body: string) {
    super.setLayoutBody(body);

    if (this.detachResize) {
      this.detachResize();
      this.attachResize();
    }
  }

  // Sized by its edges when asked to (`resizable`), and remembered under
  // `resizeState` when given one.
  private attachResize(): void {
    const options = this.renderData?.requestOptions as OverlayRequestOptionsInterface | undefined;

    if (options?.resizable && this.contentEl) {
      this.detachResize = attachOverlayResize(this.contentEl, this.app, options.resizeState ?? null);
    }
  }
}
