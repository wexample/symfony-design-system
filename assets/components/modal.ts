import Page from '@wexample/symfony-loader/js/Class/Page';
import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import RenderNode from '@wexample/symfony-loader/js/Class/RenderNode';
import FocusableComponentMixin from '@wexample/symfony-loader/js/Class/Mixins/FocusableComponentMixin';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import RequestOptionsInterface from '@wexample/symfony-loader/js/Interfaces/RequestOptions/RequestOptionsInterface';
import ConfirmService from '@wexample/symfony-loader/js/Services/ConfirmService';

interface ModalRequestOptionsInterface extends RequestOptionsInterface {
  closeOnEscape?: boolean;
  closeOnOverlayClick?: boolean;
  confirmOnClose?: boolean;
  confirmOnCloseMessage?: string;
  confirmOnCloseWhenDirty?: boolean;
}

export default class extends PageManagerComponent {
  private contentEl?: HTMLElement;
  protected fadeOpen?: () => void;
  protected closeWithAnimation?: (event?: Event) => Promise<void>;
  private closeOnOverlayClick = true;
  private confirmOnClose = false;
  private confirmOnCloseMessage = '@page::frontend.embed.closing_confirmation.message';
  private confirmOnCloseTitle = '@page::frontend.embed.closing_confirmation.title';
  private onClickOverlayProxy?: EventListener;

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

    const options = this.renderData?.requestOptions as ModalRequestOptionsInterface | undefined;
    this.closeOnOverlayClick = options?.closeOnOverlayClick !== false;
    this.confirmOnClose = options?.confirmOnClose === true;
    this.confirmOnCloseMessage = options?.confirmOnCloseMessage
      || '@page::frontend.embed.closing_confirmation.message';
    this.confirmOnCloseTitle = options?.confirmOnCloseTitle
      || '@page::frontend.embed.closing_confirmation.title';

    this.contentEl?.addEventListener('click', this.onClickContent);
    this.onClickOverlayProxy = this.onClickOverlay.bind(this) as EventListener;
    this.el.addEventListener('click', this.onClickOverlayProxy);
  }

  protected async deactivateListeners(): Promise<void> {
    this.contentEl?.removeEventListener('click', this.onClickContent);
    if (this.onClickOverlayProxy) {
      this.el.removeEventListener('click', this.onClickOverlayProxy);
    }

    await super.deactivateListeners();
  }

  public async open(options: { instant?: boolean } = {}) {
    (this as any).overlayOpen(options.instant);
  }

  public async close(options: { instant?: boolean } = {}) {
    if (this.confirmOnClose) {
      const message = this['trans']?.(this.confirmOnCloseMessage) || this.confirmOnCloseMessage;
      const title = this['trans']?.(this.confirmOnCloseTitle);
      const confirmService = this.app.getServiceOrFail(ConfirmService) as ConfirmService;

      const result = await confirmService.confirm({
        title: title || undefined,
        message: this.page['trans'](message),
        preset: 'ok_cancel',
      });
      if (result !== 'ok') {
        return;
      }
    }

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

  private onClickOverlay = async (event: Event) => {
    if (!this.closeOnOverlayClick) {
      return;
    }

    if (event.target !== this.el) {
      return;
    }

    await this.close();
  };

  overlayOnClickOutside(): void {
    if (!this.closeOnOverlayClick) {
      return;
    }

    this.close();
  }

  overlayOnEscape(): void {
    this.close();
  }

  focusableShouldHandleEscape(): boolean {
    const options = this.renderData?.requestOptions as ModalRequestOptionsInterface | undefined;
    if (options?.closeOnEscape === false) {
      return false;
    }

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
