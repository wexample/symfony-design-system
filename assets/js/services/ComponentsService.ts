import MixinsAppService from '../class/MixinsAppService';
import Page from '../class/Page';
import PromptService from './PromptsService';


import Component from '../class/Component';
import AbstractRenderNodeService from './AbstractRenderNodeService';
import RenderNode from '../class/RenderNode';
import { appendInnerHtml } from '../helpers/DomHelper';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import AppService from '../class/AppService';

export default class ComponentsService extends AbstractRenderNodeService {
  private elLayoutComponents: HTMLElement;

  public static dependencies: typeof AppService[] = [PromptService];

  public static serviceName: string = 'components';

  registerHooks() {
    return {
      app: {
        hookInit() {
          this.elLayoutComponents = document.getElementById('components-templates');
        },

        async hookLoadLayoutRenderData(
          renderData: LayoutInterface,
          registry: any
        ) {
          if (registry.assets !== MixinsAppService.LOAD_STATUS_COMPLETE) {
            return MixinsAppService.LOAD_STATUS_WAIT;
          }

          // Components like modal can contain a new layout.
          await this.app.services.components.loadLayoutRenderData(renderData);
        },
      },
      
      page: {
        async hookInitPage(page: Page) {
          await this.createRenderDataComponents(
            page
          );
        },
      },
    }
  }

  async loadLayoutRenderData(renderData: LayoutInterface) {
    if (renderData.templates) {
      // Append html for global components.
      appendInnerHtml(this.elLayoutComponents, renderData.templates);
    }

    await this.createRenderDataComponents(renderData, this.app.layout);
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
        renderDataComponent.templateAbstractPath,
        renderDataComponent,
        parentRenderNode
      )) as Component;

      parentRenderNode.components.push(component);
    }
  }
}
