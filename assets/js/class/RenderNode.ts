import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import AppChild from './AppChild';
import App from './App';
import Component from './Component';

export default abstract class RenderNode extends AppChild {
  public childRenderNodes: { [key: string]: RenderNode } = {};
  public components: Component[] = [];
  public el: HTMLElement;
  public id: string;
  public name: string;
  public parentRenderNode: RenderNode;
  public renderData: RenderDataInterface;
  public translations: {} = {};
  public vars: any = {};

  constructor(app: App, parentRenderNode?: RenderNode) {
    super(app);
    this.parentRenderNode = parentRenderNode;
  }

  public async init() {
    // Layout can have no parent node.
    if (this.parentRenderNode) {
      this.parentRenderNode.appendChildRenderNode(this);
    }
  }

  loadFirstRenderData(renderData: RenderDataInterface) {
    this.renderData = renderData;

    this.mergeRenderData(renderData);
  }

  mergeRenderData(renderData: RenderDataInterface) {
    this.id = renderData.id;
    this.name = renderData.name;

    this.translations = {
      ...this.translations,
      ...renderData.translations,
    };

    this.vars = {...this.vars, ...renderData.vars};
  }

  appendChildRenderNode(renderNode: RenderNode) {
    renderNode.parentRenderNode = this;
    this.childRenderNodes[renderNode.id] = renderNode;
  }

  eachChildRenderNode(): RenderNode[] {
    return Object.values(this.childRenderNodes);
  }

  abstract attachHtmlElements();

  async mount() {
    this.attachHtmlElements();

    await this.mounted();
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
