import PageManagerComponent from '@wexample/symfony-loader/js/Class/PageManagerComponent';
import EmbedService from '@wexample/symfony-loader/js/Services/EmbedService';
import { targetEmbeddedClick } from '../../js/Helper/TargetHelper';

export default class extends PageManagerComponent {
  protected contentEl: HTMLElement;

  private visibilityObserver?: IntersectionObserver;

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

    this.el.querySelector('.embed--close')?.addEventListener('click', () => this.closeEmbed());

    // Filled from the start when the page arrived with something in it.
    if (this.contentEl?.innerHTML.trim()) {
      this.foldNeighbours(true);
    } else if (this.options.src) {
      this.openSource();
    }
  }

  protected async unmounted(): Promise<void> {
    this.visibilityObserver?.disconnect();
    this.getEmbedService().unregister(this.options.name);

    await super.unmounted();
  }

  public getPageEl(): HTMLElement {
    return this.contentEl;
  }

  protected getLayoutBase(): string {
    return 'embed';
  }

  // A plain link of the page held here would leave the window, and what lies
  // under this with it: it opens where `target-embedded` says instead.
  protected onLinkLeaving(event: MouseEvent, link: HTMLAnchorElement): void {
    targetEmbeddedClick(this.app, event, link);
  }


  public setLayoutBody(body: string) {
    super.setLayoutBody(body);

    this.contentEl.innerHTML = body || '';
    this.foldNeighbours(Boolean(body));

    // A page arriving reopens an embed that was closed.
    if (body) {
      this.setClosed(false);
    }
  }

  /**
   * Empties the embed and takes the region holding it out of its split: the
   * gesture its close button makes, and what a page can ask for. Whatever loads
   * a page into it afterwards opens it again.
   */
  public closeEmbed(): void {
    this.pageLoadingEnd();
    this.setLayoutBody('');
    this.setClosed(true);

    this.el.dispatchEvent(new CustomEvent('embed:close', {
      bubbles: true,
      detail: { name: this.options.name },
    }));
  }

  // The page the embed opens on: at once, or — `lazy` — once the embed is on
  // screen, so a wall of them only asks for what is being looked at.
  private openSource(): void {
    const load = () => {
      void this.getEmbedService().load(this.options.name, this.options.src);
    };

    if (!this.options.lazy || !('IntersectionObserver' in window)) {
      load();

      return;
    }

    this.visibilityObserver = new IntersectionObserver((entries) => {
      if (entries.some((entry) => entry.isIntersecting)) {
        this.visibilityObserver?.disconnect();
        this.visibilityObserver = undefined;
        load();
      }
    });
    this.visibilityObserver.observe(this.el);
  }

  private setClosed(closed: boolean): void {
    this.el.classList.toggle('embed--closed', closed);
    this.splitZone()?.classList.toggle('zone--closed', closed);
  }

  // A region beside a filled embed makes room for it, if it says how far: the
  // zones of the same row declaring a folded width take it while the embed
  // holds something, and give it back once it is emptied. A width the visitor
  // dragged still wins — that is the stylesheet's business, not this one's.
  private foldNeighbours(filled: boolean): void {
    const zone = this.splitZone();

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

  // The region of a split the embed stands in: the one its neighbours are.
  private splitZone(): HTMLElement | null {
    let zone: HTMLElement | null = this.el.closest('.zone');

    while (zone && !zone.parentElement?.classList.contains('zone--split')) {
      zone = zone.parentElement?.closest('.zone') ?? null;
    }

    return zone;
  }

  // open() and close() are left as they come: an embed is part of the page it
  // sits in and is always shown, which is the whole difference with a modal or
  // a panel.

  private getEmbedService(): EmbedService {
    return this.app.getServiceOrFail(EmbedService) as EmbedService;
  }
}
