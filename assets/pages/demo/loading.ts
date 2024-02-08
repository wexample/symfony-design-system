import Page from'@wexample/symfony-design-system/js/class/Page';
import ModalsService from'@wexample/symfony-design-system/js/services/ModalsService';
import AppService from'@wexample/symfony-design-system/js/class/AppService';
import ServicesRegistryInterface from'@wexample/symfony-design-system/js/interfaces/ServicesRegistryInterface';

export default class extends Page {
  services: ServicesRegistryInterface;

  getPageLevelMixins(): typeof AppService[] {
    return [ModalsService];
  }

  async pageReady() {
    this.el
      .querySelector('#page-overlay-show')
      .addEventListener('click', () => {
        this.loadingStart();

        setTimeout(() => {
          this.loadingStop();
        }, 1000);
      });

    this.el.querySelector('#page-modal-show').addEventListener('click', () => {
      this.app.services.modals.get('/demo/loading/fetch/simple');
    });
  }
}
