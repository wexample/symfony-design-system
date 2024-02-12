import AdaptiveService from './AdaptiveService';

import RenderDataPageInterface from '../interfaces/RenderData/PageInterface';
import LayoutInterface from '../interfaces/RenderData/LayoutInterface';
import AbstractRenderNodeService from './AbstractRenderNodeService';
import Page from '../class/Page';
import RenderNode from '../class/RenderNode';
import AppService from '../class/AppService';
import ResponsiveService from "./ResponsiveService";

export default class PagesService extends AbstractRenderNodeService {
  public static dependencies: typeof AppService[] = [
    AdaptiveService,
    ResponsiveService,
  ];
  public static serviceName: string = 'pages';

  registerHooks() {
    return {
      app: {
        async hookLoadLayoutRenderData(
          renderData: LayoutInterface,
          registry: any
        ) {
          if (renderData.page) {
            await this.app.services.pages.createPage(renderData.page);
          }
          return;
        },
      },
    };
  }

  async createPage(renderData: RenderDataPageInterface) {
    let parentNode: RenderNode;

    if (renderData.isInitialPage) {
      parentNode = this.app.layout;
    }

    await this.createRenderNode(renderData.name, renderData, parentNode);
  }

  createRenderNodeInstance(
    classDefinition: any,
    parentRenderNode: RenderNode
  ): RenderNode | null {
    return super.createRenderNodeInstance(
      classDefinition || this.app.getClassPage(),
      parentRenderNode
    ) as Page;
  }
}
