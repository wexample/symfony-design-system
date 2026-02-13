import Page from '@wexample/symfony-loader/js/Class/Page';
import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import RenderNode from '@wexample/symfony-loader/js/Class/RenderNode';
import FocusableComponentMixin from '@wexample/symfony-loader/js/Class/Mixins/FocusableComponentMixin';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';

export default class extends PageManagerComponent {
  private contentEl?: HTMLElement;
  protected fadeOpen?: () => void;
  protected closeWithAnimation?: (event?: Event) => Promise<void>;

  async init() {
    FadeAnimationMixin.apply(this);
    FocusableComponentMixin.apply(this);
    OverlayMixin.apply(this);
    await super.init();
  }

  attachHtmlElements() {
    super.attachHtmlElements();

    this.contentEl = this.el.querySelector('.modal--content') as HTMLElement;

    if (this.contentEl && this.layoutBody) {
      this.contentEl.innerHTML = this.layoutBody;
    }
  }

  appendChildRenderNode(renderNode: RenderNode) {
    super.appendChildRenderNode(renderNode);

    if (renderNode instanceof Page) {
      renderNode.ready(() => {
        this.open({
          instant: this.renderData.requestOptions['instant']
        });
      });
    }
  }

  public getPageEl(): HTMLElement {
    return this.contentEl || this.el;
  }

  public setLayoutBody(body: string) {
    super.setLayoutBody(body);

    if (this.contentEl) {
      this.contentEl.innerHTML = body || '';
    }
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.contentEl?.addEventListener('click', this.onClickContent);
  }

  protected async deactivateListeners(): Promise<void> {
    this.contentEl?.removeEventListener('click', this.onClickContent);

    await super.deactivateListeners();
  }

  public async open(options: { instant?: boolean } = {}) {
    (this as any).overlayOpen(options.instant);
  }

  public async close(options: { instant?: boolean } = {}) {
    (this as any).overlayClose(options.instant);
  }

  private onClickContent = async (event: Event) => {
    const target = event.target as HTMLElement | null;
    if (!target) {
      return;
    }

    const closeLink = target.closest('.modal-close a') as HTMLElement | null;
    if (!closeLink) {
      return;
    }

    event.preventDefault();
    await this.close();
  };

  focusableShouldHandleEscape(): boolean {
    return this.el.classList.contains('is-open');
  }
  
  
  fadeAnimationGetElement(): HTMLElement {
    return this.contentEl || this.el;
  }

  async overlayOnOpen(): Promise<void> {
    if (this.fadeOpen) {
      await this.fadeOpen();
    }
    this.page?.focus();
    this.page?.notifyTreeVisible();
  }

  async overlayOnClose(): Promise<void> {
    this.page?.blur();
    this.callerPage?.focus();
  }
}
