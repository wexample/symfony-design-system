import RenderDataPageInterface from '../interfaces/RenderData/PageInterface';
import RenderNode from './RenderNode';
import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';
import { pathToTagName } from '../helpers/StringHelper';

export default class extends RenderNode {
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

    this.el.classList.add(`page-${pathToTagName(this.name)}`);
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
}
