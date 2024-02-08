import AppService from '../class/AppService';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import RenderNode from '../class/RenderNode';
import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';

export default abstract class AbstractRenderNodeService extends AppService {
  public services: ServicesRegistryInterface;

  async createRenderNode(
    definitionName: string,
    renderData: RenderDataInterface,
    parentRenderNode?: RenderNode
  ): Promise<RenderNode> {
    let classDefinition = this.app.getBundleClassDefinition(
      definitionName,
      true
    );

    let instance = this.createRenderNodeInstance(
      classDefinition,
      parentRenderNode
    );

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
