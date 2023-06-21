import Page from '../../../../src/Resources/js/class/Page';
import ModalsService from '../../../../src/Resources/js/services/ModalsService';
import AppService from '../../../../src/Resources/js/class/AppService';
import ServicesRegistryInterface from '../../../../src/Resources/js/interfaces/ServicesRegistryInterface';

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
      this.services.modals.get('/demo/loading/fetch/simple');
    });
  }
}
