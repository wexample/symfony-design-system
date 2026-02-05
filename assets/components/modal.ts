import Page from '@wexample/symfony-loader/js/Class/Page';
import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import RenderNode from '@wexample/symfony-loader/js/Class/RenderNode';

export default class extends PageManagerComponent {
  private overlayEl?: HTMLElement;
  private contentEl?: HTMLElement;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.overlayEl = this.el.querySelector('[data-modal-overlay]') as HTMLElement;
    this.contentEl = this.el.querySelector('.modal--content') as HTMLElement;

    if (this.contentEl && this.layoutBody) {
      this.contentEl.innerHTML = this.layoutBody;
    }
  }

  appendChildRenderNode(renderNode: RenderNode) {
    super.appendChildRenderNode(renderNode);

    if (renderNode instanceof Page) {
      renderNode.ready(() => {
        this.open();
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

    this.overlayEl?.addEventListener('click', this.onClickOverlay);
  }

  protected async deactivateListeners(): Promise<void> {
    this.overlayEl?.removeEventListener('click', this.onClickOverlay);

    await super.deactivateListeners();
  }

  private open() {
    this.el.removeAttribute('hidden');
    this.el.style.display = 'flex';
    this.el.classList.add('is-open');
    this.page?.focus();
  }

  private async close() {
    this.el.classList.remove('is-open');
    this.el.style.display = 'none';
    this.el.setAttribute('hidden', 'hidden');
    this.page?.blur();
    await this.exit();
    this.callerPage?.focus();
  }

  private onClickOverlay = async () => {
    await this.close();
  };
}
