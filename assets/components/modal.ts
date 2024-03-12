import Page from '../js/class/Page';
import PageManagerComponent from '../js/class/PageManagerComponent';
import Variables from '../js/helpers/Variables';
import Events from '../js/helpers/Events';
import RenderNode from '../js/class/RenderNode';

export default class ModalComponent extends PageManagerComponent {
  public closing: boolean = false;
  public onClickCloseProxy: EventListenerObject;
  public opened: boolean = false;

  mergeRenderData(renderData: ComponentInterface) {
    super.mergeRenderData(renderData);
  }

  attachHtmlElements() {
    super.attachHtmlElements();

    this.elements.content = this.el.querySelector('.modal-content');
    this.elements.content.innerHTML = this.layoutBody;
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
}
