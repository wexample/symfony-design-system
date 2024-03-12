import Page from '../js/class/Page';
import PageManagerComponent from '../js/class/PageManagerComponent';
import Keyboard from '../js/helpers/Keyboard';
import Mouse from '../js/helpers/Mouse';
import Variables from '../js/helpers/Variables';
import Events from '../js/helpers/Events';
import RenderNode from '../js/class/RenderNode';
import ComponentInterface from '../js/interfaces/RenderData/ComponentInterface';

export default class ModalComponent extends PageManagerComponent {
  public closing: boolean = false;
  public listenKeyboardKey: string[] = [Keyboard.KEY_ESCAPE];
  public mouseDownOverlayTarget: EventTarget | null;
  public mouseDownOverlayTimestamp: number | null;
  public onClickCloseProxy: EventListenerObject;
  public onMouseDownOverlayProxy: EventListenerObject;
  public onMouseUpOverlayProxy: EventListenerObject;
  public opened: boolean = false;

  mergeRenderData(renderData: ComponentInterface) {
    super.mergeRenderData(renderData);
  }

  attachHtmlElements() {
    super.attachHtmlElements();

    this.elements.content = this.el.querySelector('.modal-content');
    this.elements.content.innerHTML = this.layoutBody;
    this.elements.close = this.el.querySelector('.modal-close a');
  }

  appendChildRenderNode(renderNode: RenderNode) {
    super.appendChildRenderNode(renderNode);

    if (renderNode instanceof Page) {
      renderNode.ready(() => {
        this.open();
      });
    }
  }

  public getPageEl(): HTMLElement {
    return this.elements.content;
  }

  async onListenedKeyUp(event: KeyboardEvent) {
    if (event.key === Keyboard.KEY_ESCAPE) {
      await this.close();
    }
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.onMouseDownOverlayProxy = this.onMouseDownOverlay.bind(this);
    this.onMouseUpOverlayProxy = this.onMouseUpOverlay.bind(this);
    this.onClickCloseProxy = this.onClickClose.bind(this);

    this.el.addEventListener(Events.MOUSEDOWN, this.onMouseDownOverlayProxy);
    this.el.addEventListener(Events.MOUSEUP, this.onMouseUpOverlayProxy);
    this.elements.close.addEventListener(Events.CLICK, this.onClickCloseProxy);
  }

  protected async deactivateListeners(): Promise<void> {
    await super.deactivateListeners();

    this.el.removeEventListener(Events.MOUSEDOWN, this.onMouseDownOverlayProxy);
    this.el.removeEventListener(Events.MOUSEUP, this.onMouseUpOverlayProxy);
    this.elements.close.removeEventListener(Events.CLICK, this.onClickCloseProxy);
  }

  showEl() {
    this.el.classList.remove(Variables.CLOSED);
    this.el.classList.add(Variables.OPENED);
  }

  hideEl() {
    this.el.classList.remove(Variables.OPENED);
    this.el.classList.add(Variables.CLOSED);
  }

  open() {
    if (this.opened) {
      return;
    }

    this.opened = true;

    this.showEl();

    this.page.focus();
  }

  close() {
    this.closing = true;

    this.hideEl();

    this.page.blur();

    return new Promise(async (resolve) => {
      // Sync with CSS animation.
      await setTimeout(async () => {
        this.el.classList.remove(Variables.CLOSED);
        this.opened = this.closing = false;

        await this.exit();

        this.callerPage.focus();

        resolve(this);
      }, 400);
    });
  }

  async onClickClose() {
    await this.close();
  }

  onMouseDownOverlay(event: MouseEvent) {
    // Accept closing modal on clicking on the overlay,
    // only if the mousedown is started on the overlay itself.
    if (event.target === event.currentTarget) {
      this.mouseDownOverlayTarget = event.target;
      this.mouseDownOverlayTimestamp = Date.now();
    } else {
      this.mouseDownOverlayTarget = null;
      this.mouseDownOverlayTimestamp = null;
    }
  }

  async onMouseUpOverlay(event: MouseEvent) {
    // Check that click has been on the same element.
    // Then prevent too long clicks.
    if (
      event.target === this.mouseDownOverlayTarget &&
      Date.now() - this.mouseDownOverlayTimestamp < Mouse.CLICK_DURATION
    ) {
      await this.close();
    }
  }
}
