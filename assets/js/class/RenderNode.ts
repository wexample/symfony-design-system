import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import AppChild from './AppChild';

export default abstract class RenderNode extends AppChild {
  public childRenderNodes: { [key: string]: RenderNode } = {};
  public el: HTMLElement;
  public renderData: RenderDataInterface;

  eachChildRenderNode(): RenderNode[] {
    return Object.values(this.childRenderNodes);
  }

  abstract attachHtmlElements();

  async mount() {
    this.attachHtmlElements();
  }
  
  async mountTree() {
    await this.forEachTreeRenderNode(async (renderNode: RenderNode) => {
      await renderNode.mount();
    });
  }

  async forEachTreeRenderNode(callback?: Function) {
    await callback(this);

    await this.forEachTreeChildRenderNode(callback);
  }

  async forEachTreeChildRenderNode(callback?: Function) {
    let renderNode: RenderNode;
    for (renderNode of this.eachChildRenderNode()) {
      await renderNode.forEachTreeRenderNode(callback);
    }
  }


  protected async mounted(): Promise<void> {

  }
}
