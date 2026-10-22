import Page from '@wexample/symfony-loader/js/Class/Page';
import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import RenderNode from '@wexample/symfony-loader/js/Class/RenderNode';
import { targetEmbeddedClick } from '../../js/Helper/TargetHelper';

const DOCK_LAYER_ID = 'dock-layer';

// The row the docked windows share at the foot of the screen, made the first
// time one is opened. Out of the page's scroller, so it keeps to the window.
function dockLayer(): HTMLElement {
  let layer = document.getElementById(DOCK_LAYER_ID);

  if (!layer) {
    layer = document.createElement('div');
    layer.id = DOCK_LAYER_ID;
    layer.className = 'dock-layer';
    document.body.appendChild(layer);
  }

  return layer;
}

// A window docked at the foot of the screen: a page loaded in it (`__layout=
// dock`), or what a script put there. Beside the page, not over it — no
// backdrop, no focus held, no place in the overlay stack: the page stays
// usable while it is open. Its header folds it to its title and back; its
// close link takes it away.
export default class extends PageManagerComponent {
  protected contentEl?: HTMLElement;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.contentEl = this.el.querySelector('.dock--content') as HTMLElement;

    const layer = dockLayer();

    if (this.el.parentElement !== layer) {
      layer.appendChild(this.el);
    }

    if (this.contentEl && this.layoutBody) {
      this.contentEl.innerHTML = this.layoutBody;
    }

    // A dialog the page is not held behind.
    this.el.setAttribute('role', 'dialog');
    this.el.setAttribute('aria-modal', 'false');
  }

  public getPageEl(): HTMLElement {
    return this.contentEl || this.el;
  }

  public setLayoutBody(body: string) {
    super.setLayoutBody(body);

    if (this.contentEl) {
      this.contentEl.innerHTML = body || '';
    }

    this.labelDialog();
  }

  appendChildRenderNode(renderNode: RenderNode) {
    super.appendChildRenderNode(renderNode);

    if (renderNode instanceof Page) {
      renderNode.ready(async () => {
        await this.open();
      });
    }
  }

  protected getLayoutBase(): string {
    return 'dock';
  }

  // A plain link of the page held here opens where `target-embedded` says
  // rather than leaving the window, as from a panel.
  protected onLinkLeaving(event: MouseEvent, link: HTMLAnchorElement): void {
    targetEmbeddedClick(this.app, event, link);
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.el.addEventListener('click', this.onClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onClick);

    await super.deactivateListeners();
  }

  public async open(): Promise<void> {
    this.el.removeAttribute('hidden');
    this.el.classList.add('is-open');
    this.setCollapsed(false);
  }

  public async close(): Promise<void> {
    this.el.classList.remove('is-open');
    this.el.setAttribute('hidden', 'hidden');

    await this.exit();
  }

  public isCollapsed(): boolean {
    return this.el.classList.contains('is-collapsed');
  }

  public setCollapsed(collapsed: boolean): void {
    this.el.classList.toggle('is-collapsed', collapsed);
    this.el.querySelector('.dock-header')?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
  }

  // Named by its title, as a panel is.
  private labelDialog(): void {
    const heading = this.el.querySelector<HTMLElement>('.header-title');

    if (!heading?.textContent?.trim()) {
      this.el.removeAttribute('aria-labelledby');

      return;
    }

    heading.id = heading.id || `dock-title-${Math.random().toString(36).slice(2, 10)}`;
    this.el.setAttribute('aria-labelledby', heading.id);
  }

  private onClick = (event: MouseEvent) => {
    const target = event.target as HTMLElement | null;

    if (!target) {
      return;
    }

    if (target.closest('.dock-close a')) {
      event.preventDefault();
      void this.close();

      return;
    }

    // The header is the handle; what stands in it — the way to open the page
    // whole — keeps its own press.
    if (target.closest('.dock-header') && !target.closest('a, button')) {
      this.setCollapsed(!this.isCollapsed());
    }
  };
}
