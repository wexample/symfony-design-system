import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import EmbedService from '@wexample/symfony-loader/js/Services/EmbedService';

export default class extends PageManagerComponent {
  protected contentEl: HTMLElement;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.contentEl = this.el.querySelector('.embed--content') as HTMLElement;

    if (this.layoutBody) {
      this.contentEl.innerHTML = this.layoutBody;
    }
  }

  protected async mounted(): Promise<void> {
    await super.mounted();

    this.getEmbedService().register(this.options.name, this);

    // Filled from the start when the page arrived with something in it.
    if (this.contentEl?.innerHTML.trim()) {
      this.foldNeighbours(true);
    }
  }

  protected async unmounted(): Promise<void> {
    this.getEmbedService().unregister(this.options.name);

    await super.unmounted();
  }

  public getPageEl(): HTMLElement {
    return this.contentEl;
  }

  protected getLayoutBase(): string {
    return 'embed';
  }

  public setLayoutBody(body: string) {
    super.setLayoutBody(body);

    this.contentEl.innerHTML = body || '';
    this.foldNeighbours(Boolean(body));
  }

  // A region beside a filled embed makes room for it, if it says how far: the
  // zones of the same row declaring a folded width take it while the embed
  // holds something, and give it back once it is emptied. A width the visitor
  // dragged still wins — that is the stylesheet's business, not this one's.
  private foldNeighbours(filled: boolean): void {
    let zone: HTMLElement | null = this.el.closest('.zone');

    while (zone && !zone.parentElement?.classList.contains('zone--split')) {
      zone = zone.parentElement?.closest('.zone') ?? null;
    }

    zone?.parentElement
      ?.querySelectorAll<HTMLElement>(':scope > .zone[data-zone-folded-size]')
      .forEach((neighbour) => {
        if (neighbour === zone) {
          return;
        }

        neighbour.style.setProperty('--zone-folded-size', neighbour.dataset.zoneFoldedSize ?? '');
        neighbour.classList.toggle('zone--folded', filled);
      });
  }

  // open() and close() are left as they come: an embed is part of the page it
  // sits in and is always shown, which is the whole difference with a modal or
  // a panel.

  private getEmbedService(): EmbedService {
    return this.app.getServiceOrFail(EmbedService) as EmbedService;
  }
}
