import Page from '../class/Page';

import AbstractRenderNodeService from './AbstractRenderNodeService';
import RenderNode from '../class/RenderNode';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';

export default class ComponentsService extends AbstractRenderNodeService {
  public static serviceName: string = 'components';

  registerHooks() {
    return {
      page: {
        async hookInitPage(page: Page) {
          await this.createRenderDataComponents(
            page
          );
        },
      },
    }
  }

  createRenderDataComponents(
    parentRenderNode: RenderNode,
    renderData: RenderDataInterface | null = null,
  ) {
    renderData = renderData || parentRenderNode.renderData;

    for (const renderDataComponent of renderData.components) {
      // Share request options.
      renderDataComponent.requestOptions = renderData.requestOptions;
    }
  }
}
