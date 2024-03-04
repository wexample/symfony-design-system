import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import AppChild from './AppChild';
import App from './App';
import Component from './Component';
import { toKebab } from "../helpers/StringHelper";

export class RenderNodeServiceEvents {
  public static USAGE_UPDATED: string = 'usage-changed';
}


export default abstract class RenderNode extends AppChild {
  public childRenderNodes: { [key: string]: RenderNode } = {};
  public components: Component[] = [];
  public cssClassName: string;
  public el: HTMLElement;
  public elHeight: number = 0;
  public elWidth: number = 0;
  public id: string;
  public name: string;
  public parentRenderNode: RenderNode;
  public renderData: RenderDataInterface;
  public translations: {} = {};
  public usages: {} = {};
  public vars: any = {};

  constructor(app: App, parentRenderNode?: RenderNode) {
    super(app);
    this.parentRenderNode = parentRenderNode;
  }

  public async init() {
    this.app.services.mixins.applyMethods(this, 'renderNode');
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
    this.cssClassName = renderData.cssClassName;
    this.id = renderData.id;
    this.name = renderData.name;

    this.translations = {
      ...this.translations,
      ...renderData.translations,
    };

    this.vars = {...this.vars, ...renderData.vars};
    this.usages = {...this.usages, ...renderData.usages};
  }

  appendChildRenderNode(renderNode: RenderNode) {
    renderNode.parentRenderNode = this;
    this.childRenderNodes[renderNode.id] = renderNode;
  }

  eachChildRenderNode(): RenderNode[] {
    return Object.values(this.childRenderNodes);
  }

  attachHtmlElements() {
  }

  async mount() {
    this.attachHtmlElements();
    this.updateElSize();
    await this.activateListeners();

    await this.mounted();
  }

  async unmount() {
    await this.deactivateListeners();
    await this.unmounted();
  }

  async mountTree() {
    await this.forEachTreeRenderNode(async (renderNode: RenderNode) => {
      await renderNode.mount();
    });
  }

  async setNewTreeRenderNodeReady() {
    await this.forEachTreeRenderNode(async (renderNode: RenderNode) => {
      if (!renderNode.isReady) {
        await renderNode.renderNodeReady();
      }
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

  updateElSize() {
    let rect = this.el.getBoundingClientRect();

    this.elWidth = rect.width;
    this.elHeight = rect.height;
  }

  getElWidth(): number {
    return this.elWidth;
  }

  getElHeight(): number {
    return this.elHeight;
  }

  protected async activateListeners(): Promise<void> {
  }

  protected async deactivateListeners(): Promise<void> {
  }

  protected async mounted(): Promise<void> {
    await this.app.services.mixins.invokeUntilComplete(
      'hookMounted',
      'renderNode',
      [this]
    );
  }

  protected async unmounted(): Promise<void> {

    await this.app.services.mixins.invokeUntilComplete(
      'hookUnmounted',
      'renderNode',
      [this]
    );
  }

  public async renderNodeReady(): Promise<void> {
    await this.readyComplete();
  }

  async setUsage(
    usageName: string,
    usageValue: string,
    updateAssets: boolean
  ) {
    let classList = document.body.classList;
    let usageKebab = toKebab(usageName)

    this.usages[usageName] = usageValue;

    classList.forEach((className: string) => {
      if (className.startsWith(`usage-${usageKebab}-`)) {
        classList.remove(className);
      }
    });

    classList.add(`usage-${usageKebab}-${usageValue}`);

    await this.forEachTreeChildRenderNode(async (renderNode: RenderNode) => {
      await renderNode.setUsage(
        usageName,
        usageValue,
        updateAssets,
      );
    });
  }
}
