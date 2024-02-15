import RenderDataPageInterface from '../interfaces/RenderData/PageInterface';
import RenderNode from './RenderNode';
import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';

export default class extends RenderNode {
  public isInitialPage: boolean;
  public renderData: RenderDataPageInterface;
  public services: ServicesRegistryInterface;

  attachHtmlElements() {
    let el: HTMLElement;

    if (this.renderData.isInitialPage) {
      el = this.app.layout.el;
    }

    if (el) {
      this.el = el;
    } else {
      this.app.services.prompt.systemError('page_message.error.page_missing_el');
    }

    this.el.classList.add(`page-${this.cssClassName}`);
  }

  mergeRenderData(renderData: RenderDataPageInterface) {
    super.mergeRenderData(renderData);

    this.isInitialPage = renderData.isInitialPage;

    if (this.isInitialPage) {
      this.app.layout.page = this;
    }
  }

  public async init() {
    await super.init();

    await this.app.services.mixins.invokeUntilComplete(
      'hookInitPage',
      'page',
      [
        this,
      ]
    );
  }

  public async renderNodeReady(): Promise<void> {
    await super.renderNodeReady();

    await this.pageReady();
  }

  getElWidth(): number {
    // Initial page uses layout width for responsiveness calculation.
    return this.isInitialPage
      ? this.app.layout.getElWidth()
      : super.getElWidth();
  }

  pageReady() {
    // To override.
  }
}
