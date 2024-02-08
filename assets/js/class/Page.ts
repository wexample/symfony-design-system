import RenderNode from './RenderNode';

import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';

export default class extends RenderNode {
  public services: ServicesRegistryInterface;

  attachHtmlElements() {
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
