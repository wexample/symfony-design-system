import Page from '@wexample/symfony-loader/js/Class/Page';
import BannerService from "@wexample/symfony-loader/js/Services/BannerService";

export default class extends Page {
  pageReady() {
    const layoutBannerButton = this.el?.querySelector('.layout-banner-button') as HTMLElement;
    if (!layoutBannerButton) {
      return;
    }

    const bannerTypes = ['info', 'success', 'warning', 'error'];
    let bannerIndex = 0;
    layoutBannerButton.addEventListener('click', () => {
      const type = bannerTypes[bannerIndex % bannerTypes.length];
      const message =
        layoutBannerButton.getAttribute(`data-banner-message-${type}`) ||
        'Layout banner message.';
      bannerIndex += 1;
      (this.app.getServiceOrFail(BannerService) as any).show({
        type,
        message
      });
    });
  }
}
