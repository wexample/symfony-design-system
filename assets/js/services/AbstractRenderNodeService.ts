import AppService from '../class/AppService';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import RenderNode from '../class/RenderNode';
import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';

export default abstract class AbstractRenderNodeService extends AppService {
  public services: ServicesRegistryInterface;

  /**
   * Prepare raw data object, for example make assets definition unique across
   * the several render nodes (one single css file for every rendered node).
   */
  public async prepareRenderData(renderData: RenderDataInterface): Promise<any> {
    renderData.requestOptions = renderData.requestOptions || {};

    const response = await this.app.services.mixins.invokeUntilComplete(
      'hookPrepareRenderData',
      'app',
      [renderData]
    );

    // Do not deep freeze as sub-parts might be prepared later.
    Object.seal(renderData);

    return response;
  }

  async createRenderNode(
    definitionName: string,
    renderData: RenderDataInterface,
    parentRenderNode?: RenderNode
  ): Promise<RenderNode> {
    await this.prepareRenderData(renderData);

    await this.app.services.mixins.invokeUntilComplete(
      'hookBeforeCreate',
      'renderNode',
      [definitionName, renderData, parentRenderNode]
    );

    let classDefinition = this.app.getBundleClassDefinition(
      definitionName,
      true
    );

    let instance;
    try {
      instance = this.createRenderNodeInstance(
        classDefinition,
        parentRenderNode
      );
    } catch {
      this.app.services.prompt.systemError(
        'Unable to find component with type ":type"',
        {
          ":type": definitionName
        }
      );
      return;
    }


    instance.loadFirstRenderData(renderData);

    await instance.init();

    return instance;
  }

  createRenderNodeInstance(
    classDefinition: any,
    parentRenderNode: RenderNode
  ): RenderNode | null {
    return new classDefinition(this.app, parentRenderNode);
  }
}
