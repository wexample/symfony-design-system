import AppService from '../class/AppService';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import RenderNode from '../class/RenderNode';

export default abstract class AbstractRenderNodeService extends AppService {
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

    return instance;
  }

  createRenderNodeInstance(
    classDefinition: any,
    parentRenderNode: RenderNode
  ): RenderNode | null {
    return new classDefinition(this.app, parentRenderNode);
  }
}
