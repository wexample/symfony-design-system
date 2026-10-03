import Page from '@wexample/symfony-loader/js/Class/Page';
import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import RenderNode from '@wexample/symfony-loader/js/Class/RenderNode';
import FocusableComponentMixin from '@wexample/symfony-loader/js/Class/Mixins/FocusableComponentMixin';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import RequestOptionsInterface from '@wexample/symfony-loader/js/Interfaces/RequestOptions/RequestOptionsInterface';
import ConfirmService from '@wexample/symfony-design-system/js/Services/ConfirmService';
import KeyboardService from '@wexample/symfony-loader/js/Services/KeyboardService';
import EventsService from '@wexample/symfony-loader/js/Services/EventsService';
import { hashParamDelete } from '../Helper/HashStateHelper';
import { focusTrapCanReturn, focusTrapFocusables, focusTrapNext } from '../Helper/FocusTrapHelper';

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
  // What a modal or a panel says once it has closed.
  public static readonly EVENT_CLOSED = 'overlay-page:closed';

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
  // Where the keyboard was before the overlay opened, given back on close.
  protected returnFocusEl: HTMLElement | null = null;

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

    // A dialog to assistive technologies, and one that holds the page behind
    // it out of reach. The box can take focus itself, so opening hands it the
    // keyboard before anything inside is chosen.
    const dialogEl = this.getDialogEl();
    dialogEl.setAttribute('role', 'dialog');
    dialogEl.setAttribute('aria-modal', 'true');
    dialogEl.setAttribute('tabindex', '-1');
  }

  protected getDialogEl(): HTMLElement {
    return this.contentEl || this.el;
  }

  // Named by its first heading, which the page it holds has written.
  protected labelDialog() {
    const dialogEl = this.getDialogEl();
    const heading = dialogEl.querySelector<HTMLElement>('h1, h2, h3, [data-dialog-title]');

    if (!heading) {
      dialogEl.removeAttribute('aria-labelledby');
      return;
    }

    if (!heading.id) {
      heading.id = `dialog-title-${Math.random().toString(36).slice(2, 10)}`;
    }

    dialogEl.setAttribute('aria-labelledby', heading.id);
  }

  appendChildRenderNode(renderNode: RenderNode) {
    super.appendChildRenderNode(renderNode);

    if (renderNode instanceof Page) {
      // Awaited: whoever loaded the page gets it back once the overlay is
      // open and the page holds the focus, not while it is still fading in.
      renderNode.ready(async () => {
        await this.open({
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

    // Tab and Shift+Tab go round inside the dialog, never into the page
    // behind — while it is the overlay on top: a confirm opened over it
    // holds the keyboard its own way.
    this.app.services.keyboard.registerKeyDown(
      this,
      KeyboardService.KEY_TAB,
      (event: KeyboardEvent) => this.trapTab(event),
      {
        priority: 100,
        enabled: () => (this as any).overlayIsOpen() && this.app.services.overlay.getActiveOverlay?.() === this,
      }
    );
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
    await (this as any).overlayOpen(options.instant);
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

    await (this as any).overlayClose(options.instant);

    // Said once closed, for what the page under it shows to read itself again:
    // a list a modal added to (a collection listens through its
    // `getCollectionRefreshEvents()`).
    this.app.getServiceOrFail(EventsService).trigger(AbstractOverlayPageManager.EVENT_CLOSED);
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

  private trapTab(event: KeyboardEvent): boolean {
    const dialogEl = this.getDialogEl();
    const focusables = focusTrapFocusables(dialogEl);
    const active = document.activeElement as HTMLElement | null;
    const next = focusables.length
      ? focusTrapNext(focusables, active && focusables.includes(active) ? active : null, event.shiftKey)
      : dialogEl;

    if (!next) {
      return false;
    }

    event.preventDefault();
    next.focus();

    return true;
  }

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
    const active = document.activeElement;
    this.returnFocusEl = active && !this.el.contains(active) && focusTrapCanReturn(active) ? active : null;
    this.labelDialog();

    await (this as FadeAnimationMixin).fadeOpen();
    this.page?.focus();
    this.page?.notifyTreeVisible();

    // The keyboard follows the eye into the dialog, onto the box itself: its
    // name is read, and Tab goes on to what it holds.
    this.getDialogEl().focus({ preventScroll: true });
  }

  async overlayOnClose(): Promise<void> {
    hashParamDelete(...this.getHashKeys());
    this.page?.blur();
    this.callerPage?.focus();

    // Back where it was taken from — the row, the button that opened it.
    if (focusTrapCanReturn(this.returnFocusEl)) {
      this.returnFocusEl.focus({ preventScroll: true });
    }
    this.returnFocusEl = null;
  }
}
