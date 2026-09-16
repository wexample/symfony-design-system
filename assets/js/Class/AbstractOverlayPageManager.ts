import Page from '@wexample/symfony-loader/js/Class/Page';
import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import RenderNode from '@wexample/symfony-loader/js/Class/RenderNode';
import FocusableComponentMixin from '@wexample/symfony-loader/js/Class/Mixins/FocusableComponentMixin';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import RequestOptionsInterface from '@wexample/symfony-loader/js/Interfaces/RequestOptions/RequestOptionsInterface';
import ConfirmService from '@wexample/symfony-loader/js/Services/ConfirmService';
import { hashParamDelete } from '../Helper/HashStateHelper';

export interface OverlayRequestOptionsInterface extends RequestOptionsInterface {
  closeOnEscape?: boolean;
  closeOnOverlayClick?: boolean;
  confirmOnClose?: boolean;
  confirmOnCloseMessage?: string;
  confirmOnCloseTitle?: string;
  confirmOnCloseDirtyMessage?: string;
  confirmOnCloseWhenDirty?: boolean;
}

export default abstract class AbstractOverlayPageManager extends PageManagerComponent {
  protected contentEl?: HTMLElement;

  protected closeOnOverlayClick = true;
  protected confirmOnClose = false;
  protected confirmOnCloseMessage = '@page::frontend.embed.closing_confirmation.message';
  protected confirmOnCloseTitle = 'WexampleSymfonyLoaderBundle.common.system::frontend.confirm.title';
  protected confirmOnCloseDirtyMessage = 'WexampleSymfonyLoaderBundle.common.system::frontend.embed.confirm.form_leave';
  protected onMouseDownOverlayProxy?: EventListener;
  protected onMouseUpOverlayProxy?: EventListener;
  protected pressStartedOnOverlay = false;
  protected confirmOnCloseWhenDirty = false;
  protected isDirty = false;
  protected onFormDirtyProxy?: EventListener;

  protected abstract getContentSelector(): string;
  protected abstract getCloseLinkSelector(): string;
  protected abstract getHashKeys(): [string, string, string];
  protected get useScopedMainClass(): boolean { return false; }

  async init() {
    FadeAnimationMixin.apply(this);
    FocusableComponentMixin.apply(this);
    OverlayMixin.apply(this);
    (this as any).overlayBackdropTarget = 'main';
    await super.init();
  }

  attachHtmlElements() {
    super.attachHtmlElements();

    this.contentEl = this.el.querySelector(this.getContentSelector()) as HTMLElement;

    if ((this as any).overlayBackdropTarget === 'main') {
      if (this.useScopedMainClass) {
        this.el.classList.add('is-scoped-main');
      }
      const scopeOverlayEl = document.getElementById('overlay-layer-main') as HTMLElement;
      const scopeContainer = scopeOverlayEl?.parentElement as HTMLElement;
      if (scopeContainer && this.el.parentElement !== scopeContainer) {
        scopeContainer.appendChild(this.el);
      }
    } else if (this.useScopedMainClass) {
      this.el.classList.remove('is-scoped-main');
    }

    if (this.contentEl && this.layoutBody) {
      this.contentEl.innerHTML = this.layoutBody;
    }
  }

  appendChildRenderNode(renderNode: RenderNode) {
    super.appendChildRenderNode(renderNode);

    if (renderNode instanceof Page) {
      renderNode.ready(() => {
        this.open({
          instant: this.renderData.requestOptions['instant'],
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

    const options = this.renderData?.requestOptions as OverlayRequestOptionsInterface | undefined;
    this.closeOnOverlayClick = options?.closeOnOverlayClick !== false;
    this.confirmOnClose = options?.confirmOnClose === true;
    this.confirmOnCloseMessage = options?.confirmOnCloseMessage || this.confirmOnCloseMessage;
    this.confirmOnCloseTitle = options?.confirmOnCloseTitle || this.confirmOnCloseTitle;
    this.confirmOnCloseDirtyMessage = options?.confirmOnCloseDirtyMessage || this.confirmOnCloseDirtyMessage;
    this.confirmOnCloseWhenDirty = options?.confirmOnCloseWhenDirty === true;

    this.contentEl?.addEventListener('click', this.onClickContent);
    this.onMouseDownOverlayProxy = this.onMouseDownOverlay.bind(this) as EventListener;
    this.el.addEventListener('mousedown', this.onMouseDownOverlayProxy);
    this.onMouseUpOverlayProxy = this.onMouseUpOverlay.bind(this) as EventListener;
    this.el.addEventListener('mouseup', this.onMouseUpOverlayProxy);
    this.onFormDirtyProxy = this.onFormDirty.bind(this) as EventListener;
    this.el.addEventListener('form:dirty', this.onFormDirtyProxy);
  }

  protected async deactivateListeners(): Promise<void> {
    this.contentEl?.removeEventListener('click', this.onClickContent);
    if (this.onMouseDownOverlayProxy) {
      this.el.removeEventListener('mousedown', this.onMouseDownOverlayProxy);
    }
    if (this.onMouseUpOverlayProxy) {
      this.el.removeEventListener('mouseup', this.onMouseUpOverlayProxy);
    }
    if (this.onFormDirtyProxy) {
      this.el.removeEventListener('form:dirty', this.onFormDirtyProxy);
    }

    await super.deactivateListeners();
  }

  public async open(options: { instant?: boolean } = {}) {
    (this as any).overlayOpen(options.instant);
  }

  public async close(options: { instant?: boolean; userInitiated?: boolean } = {}) {
    const shouldConfirm =
      options.userInitiated &&
      (this.confirmOnClose || (this.confirmOnCloseWhenDirty && this.isDirty));

    if (shouldConfirm) {
      const title = this.page['trans'](this.confirmOnCloseTitle);
      const confirmService = this.app.getServiceOrFail(ConfirmService) as ConfirmService;

      const messageKey =
        this.confirmOnCloseWhenDirty && this.isDirty
          ? this.confirmOnCloseDirtyMessage
          : this.confirmOnCloseMessage;
      const result = await confirmService.confirm({
        title: title || undefined,
        message: this.page['trans'](messageKey),
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

    const closeLink = target.closest(this.getCloseLinkSelector()) as HTMLElement | null;
    if (!closeLink) {
      return;
    }

    event.preventDefault();
    await this.close({ userInitiated: true });
  };

  // Closing asks for a full click on the backdrop — pressed there and released
  // there. The `click` event cannot say that: after a drag it fires on the
  // common ancestor of the two ends, so a text selection started in a field and
  // released past the content would read as a click on the backdrop, and close
  // the modal over a form in progress.
  private onMouseDownOverlay = (event: Event) => {
    this.pressStartedOnOverlay = event.target === this.el;
  };

  private onMouseUpOverlay = async (event: Event) => {
    const startedOnOverlay = this.pressStartedOnOverlay;
    this.pressStartedOnOverlay = false;

    if (!this.closeOnOverlayClick || !startedOnOverlay || event.target !== this.el) {
      return;
    }

    await this.close({ userInitiated: true });
  };

  private onFormDirty(event: CustomEvent) {
    if (event.detail?.dirty === true) {
      this.isDirty = true;
    }
  }

  overlayOnClickOutside(): void {
    if (!this.closeOnOverlayClick) {
      return;
    }

    this.close({ userInitiated: true });
  }

  overlayOnEscape(): void {
    this.close({ userInitiated: true });
  }

  focusableShouldHandleEscape(): boolean {
    const options = this.renderData?.requestOptions as OverlayRequestOptionsInterface | undefined;
    if (options?.closeOnEscape === false) {
      return false;
    }

    return this.el.classList.contains('is-open');
  }

  fadeAnimationGetElement(): HTMLElement {
    return this.contentEl || this.el;
  }

  async overlayOnOpen(): Promise<void> {
    await (this as FadeAnimationMixin).fadeOpen();
    this.page?.focus();
    this.page?.notifyTreeVisible();
  }

  async overlayOnClose(): Promise<void> {
    hashParamDelete(...this.getHashKeys());
    this.page?.blur();
    this.callerPage?.focus();
  }
}
