import PageManagerComponent from "../js/class/PageManagerComponent";

export default class PageHandlerComponent extends PageManagerComponent {
  attachHtmlElements() {
    super.attachHtmlElements();
    console.log('ATACH PAGE HANDLER')
  }

  public getPageEl(): HTMLElement {
    console.log('getPageEl PAGE HANDLER')
    return this.elements.content;
  }
}