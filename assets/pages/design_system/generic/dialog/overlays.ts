import Page from '@wexample/symfony-loader/js/Class/Page';
import OverlayService from '@wexample/symfony-loader/js/Services/OverlayService';

export default class extends Page {
  private overlayHandle: { close: () => Promise<void> } | null = null;

  pageReady() {
    const overlayService = this.app.getServiceOrFail(OverlayService) as OverlayService;

    const attach = (selector: string, options: { contentHtml?: string } = {}) => {
      const button = this.el?.querySelector(selector) as HTMLElement;
      if (!button) {
        return;
      }

      button.addEventListener('click', async () => {
        if (this.overlayHandle) {
          await this.overlayHandle.close();
          this.overlayHandle = null;
        }

        const created = await overlayService.showStandalone({
          timeout: 3000,
          ...(options.contentHtml !== undefined ? { contentHtml: options.contentHtml } : {})
        });
        this.overlayHandle = created;
      });
    };

    attach('.layout-banner-button', { contentHtml: '' });
    attach('.layout-spinner-button');
  }
}
