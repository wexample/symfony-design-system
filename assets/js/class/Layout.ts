import Page from './Page';
import RenderNode from './RenderNode';
import RenderDataInterface from "../interfaces/RenderData/RenderDataInterface";

export default abstract class extends RenderNode {
  public page: Page;
  public renderRequestId: string;

  mergeRenderData(renderData: RenderDataInterface) {
    super.mergeRenderData(renderData)

    this.renderRequestId = renderData.renderRequestId;
  }
}
