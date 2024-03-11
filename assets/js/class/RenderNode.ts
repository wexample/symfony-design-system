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
  public isMounted: null | boolean = null;
  public templateAbstractPath: string;
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
    this.templateAbstractPath = renderData.templateAbstractPath;

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

  async mountOnce() {
    // When render nodes are attached to the tree,
    // the whole layout try to mount the newly created render nodes,
    // so we should prevent it to be mounted twice.
    if (this.isMounted === null) {
      await this.mount();
    }
  }

  async mount() {
    if (this.isMounted === true) {
      return;
    }

    this.isMounted = true;

    this.attachHtmlElements();
    this.updateElSize();
    await this.activateListeners();

    await this.mounted();
  }

  async unmount() {
    if (!this.isMounted === false) {
      return;
    }

    this.isMounted = false;

    this.detachHtmlElements();

    await this.deactivateListeners();
    await this.unmounted();
  }

  async mountTree() {
    await this.forEachTreeRenderNode(async (renderNode: RenderNode) => {
      await renderNode.mountOnce();
    });
  }

  async setNewTreeRenderNodeReady() {
    await this.forEachTreeRenderNode(async (renderNode: RenderNode) => {
      if (!renderNode.isReady) {
        await renderNode.renderNodeReady();
      }
    });
  }

  detachHtmlElements() {
    this.el.remove();
    delete this.el;
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
    initial: boolean = false
  ) {
    if (!initial && this.app.layout.vars['usagesConfig'][usageName]['list'][usageValue]['allow_switch'] == false) {
      this.app.services.prompt.systemError(
        'Switching is not allowed for usage ":usage" and value ":value"',
        {
          ':usage': usageName,
          ':value': usageValue,
        });
      return;
    }

    let classList = document.body.classList;
    let usageKebab = toKebab(usageName)

    this.usages[usageName] = usageValue;

    classList.forEach((className: string) => {
      if (className.startsWith(`usage-${usageKebab}-`)) {
        classList.remove(className);
      }
    });

    classList.add(`usage-${usageKebab}-${usageValue}`);

    // Propagate
    this.forEachTreeChildRenderNode((renderNode: RenderNode) => {
      renderNode.setUsage(
        usageName,
        usageValue,
        initial
      )
    })
  }
}
