import Page from '../js/class/Page';
import PageManagerComponent from '../js/class/PageManagerComponent';
import Variables from '../js/helpers/Variables';
import Events from '../js/helpers/Events';
import RenderNode from '../js/class/RenderNode';

export default class ModalComponent extends PageManagerComponent {
  public focused: boolean = false;
  public closing: boolean = false;
  public onClickCloseProxy: EventListenerObject;
  public opened: boolean = false;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.elements.content = this.el.querySelector('.modal-content');
  }

  appendChildRenderNode(renderNode: RenderNode) {
    super.appendChildRenderNode(renderNode);

    if (renderNode instanceof Page) {
      renderNode.ready(() => {
        this.open();
      });
    }
  }

  public renderPageEl(page: Page): HTMLElement {
    this.elements.content.innerHTML = page.renderData.body;

    this.el
      .querySelector('.modal-close a')
      .addEventListener(Events.CLICK, this.onClickCloseProxy);

    return this.getPageEl();
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
  }
}
