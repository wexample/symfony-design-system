import Page from '../class/Page';
import PromptService from './PromptsService';


import Component from '../class/Component';
import AbstractRenderNodeService from './AbstractRenderNodeService';
import RenderNode from '../class/RenderNode';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import AppService from '../class/AppService';

export default class ComponentsService extends AbstractRenderNodeService {
  public static dependencies: typeof AppService[] = [PromptService];

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

  createRenderNodeInstance(
    classDefinition: any,
    parentRenderNode: RenderNode
  ): RenderNode | null {
    // Prevent multiple alerts for the same component.
    if (!classDefinition) {
      this.app.services.prompt.systemError(
        'page_message.error.com_missing',
        {},
        classDefinition
      );
    } else {
      return super.createRenderNodeInstance(
        classDefinition,
        parentRenderNode
      ) as Component;
    }
  }

  async createRenderDataComponents(
    parentRenderNode: RenderNode,
    renderData: RenderDataInterface | null = null,
  ) {
    renderData = renderData || parentRenderNode.renderData;

    for (const renderDataComponent of renderData.components) {
      // Share request options.
      renderDataComponent.requestOptions = renderData.requestOptions;

      let component = (await this.createRenderNode(
        renderDataComponent.name,
        renderDataComponent,
        parentRenderNode
      )) as Component;

      parentRenderNode.components.push(component);
    }
  }
}
