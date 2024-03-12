import AdaptiveService from './AdaptiveService';
import LocaleService from './LocaleService';
import MixinsAppService from '../class/MixinsAppService';
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
    LocaleService,
  ];
  public static serviceName: string = 'pages';

  registerHooks() {
    return {
      app: {
        async hookLoadLayoutRenderData(
          renderData: LayoutInterface,
          registry: any
        ) {
          if (
            registry.components === MixinsAppService.LOAD_STATUS_COMPLETE &&
            registry.responsive === MixinsAppService.LOAD_STATUS_COMPLETE &&
            registry.locale === MixinsAppService.LOAD_STATUS_COMPLETE
          ) {
            if (renderData.page) {
              await this.app.services.pages.createPageFromLayoutRenderData(renderData, renderData.page);
            }
            return;
          }

          return MixinsAppService.LOAD_STATUS_WAIT;
        },
      },
    };
  }

  async createPageFromLayoutRenderData(renderData: LayoutInterface) {
    let parentNode: RenderNode;

    if (renderData.page.isInitialPage) {
      parentNode = this.app.layout;
    }

    const registry = this.app.services.components.pageHandlerRegistry;
    let pageHandler = registry[renderData.renderRequestId];

    if (pageHandler) {
      parentNode = pageHandler;
      pageHandler.setLayoutBody(renderData.body)

      delete registry[renderData.renderRequestId];
    }

    await this.createRenderNode(
      renderData.renderRequestId,
      renderData.page.templateAbstractPath,
      renderData.page,
      parentNode
    );
  }

  createRenderNodeInstance(
    renderRequestId: string,
    classDefinition: any,
    parentRenderNode: RenderNode
  ): RenderNode | null {
    return super.createRenderNodeInstance(
      renderRequestId,
      classDefinition || this.app.getClassPage(),
      parentRenderNode
    ) as Page;
  }
}
