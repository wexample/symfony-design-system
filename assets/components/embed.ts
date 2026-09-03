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
  }

  protected async unmounted(): Promise<void> {
    this.getEmbedService().unregister(this.options.name);

    await super.unmounted();
  }

  public getPageEl(): HTMLElement {
    return this.contentEl;
  }

  public setLayoutBody(body: string) {
    super.setLayoutBody(body);

    this.contentEl.innerHTML = body || '';
  }

  // open() and close() are left as they come: an embed is part of the page it
  // sits in and is always shown, which is the whole difference with a modal or
  // a panel.

  private getEmbedService(): EmbedService {
    return this.app.getServiceOrFail(EmbedService) as EmbedService;
  }
}
