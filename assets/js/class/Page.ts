import PageResponsiveDisplay from './PageResponsiveDisplay';
import RenderDataPageInterface from '../interfaces/RenderData/PageInterface';
import RenderNode from './RenderNode';
import PageManagerComponent from './PageManagerComponent';
import AppService from './AppService';
import { ColorSchemeServiceEvents } from '../services/ColorSchemeService';
import { ResponsiveServiceEvents } from '../services/ResponsiveService';
import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';
import { pathToTagName } from '../helpers/StringHelper';

export default class extends RenderNode {
  public elOverlay: HTMLElement;
  public isInitialPage: boolean;
  public name: string;
  protected onChangeResponsiveSizeProxy: Function;
  protected onChangeColorSchemeProxy: Function;
  public parentRenderNode: PageManagerComponent;
  public renderData: RenderDataPageInterface;
  public responsiveDisplayCurrent: PageResponsiveDisplay;
  public services: ServicesRegistryInterface;

  public getRenderNodeType(): string {
    return 'page';
  }

  getPageLevelMixins(): typeof AppService[] {
    return [];
  }

  attachHtmlElements() {
    let el: HTMLElement;

    if (this.renderData.isInitialPage) {
      el = this.app.layout.el;
    } else if (this.parentRenderNode instanceof PageManagerComponent) {
      el = this.parentRenderNode.getPageEl();
    }

    if (el) {
      this.el = el;
    } else {
      this.app.services.prompt.systemError('Unable to find DOM HTMLElement for page');
    }

    this.el.classList.add(`page-${buildStringIdentifier(this.view)}`);

    this.elOverlay = this.el.querySelector('.page-overlay');
  }

  mergeRenderData(renderData: RenderDataPageInterface) {
    super.mergeRenderData(renderData);

    this.isInitialPage = renderData.isInitialPage;

    if (this.isInitialPage) {
      this.app.layout.page = this;
    }
  }

  public async init() {
    await super.init();

    await this.app.loadAndInitServices(this.getPageLevelMixins());

    // The initial layout is a page manager component.
    if (this.parentRenderNode instanceof PageManagerComponent) {
      this.parentRenderNode.setPage(this);
    }

    await this.app.services.mixins.invokeUntilComplete(
      'hookInitPage',
      'page',
      [
        this,
      ]
    );

    if (!this.app.layout.pageFocused) {
      this.focus();
    }
  }

  public async mounted() {
    this.activateMountedListeners();

    await super.mounted();

    this.focus();
  }

  public async unmounted() {
    this.deactivateMountedListeners();

    await super.unmounted();

    this.focus();
  }

  public async renderNodeReady(): Promise<void> {
    await super.renderNodeReady();

    await this.pageReady();
  }

  public focus() {
    this.activateFocusListeners();

    this.app.layout.pageFocused && this.app.layout.pageFocused.blur();
    this.app.layout.pageFocused = this;
  }

  public blur() {
    this.deactivateFocusListeners();
  }

  protected activateFocusListeners(): void {
    // To override.
  }

  protected deactivateFocusListeners(): void {
    // To override.
  }

  protected activateMountedListeners(): void {
    this.onChangeColorSchemeProxy = this.onChangeColorScheme.bind(this);



    this.app.services.events.listen(
      ColorSchemeServiceEvents.COLOR_SCHEME_CHANGE,
      this.onChangeColorSchemeProxy
    );
  }

  protected deactivateMountedListeners(): void {
    this.app.services.events.forget(
      ResponsiveServiceEvents.RESPONSIVE_CHANGE_SIZE,
      this.onChangeResponsiveSizeProxy
    );

    this.app.services.events.forget(
      ColorSchemeServiceEvents.COLOR_SCHEME_CHANGE,
      this.onChangeResponsiveSizeProxy
    );
  }

  getElWidth(): number {
    // Initial page uses layout width for responsiveness calculation.
    return this.isInitialPage
      ? this.app.layout.getElWidth()
      : super.getElWidth();
  }

  loadingStart() {
    this.elOverlay.style.display = 'block';
  }

  loadingStop() {
    this.elOverlay.style.display = 'none';
  }

  pageReady() {
    // To override.
  }
}
