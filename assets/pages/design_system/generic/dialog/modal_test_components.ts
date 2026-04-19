import Page from '@wexample/symfony-loader/js/Class/Page';
import ToastService from "@wexample/symfony-loader/js/Services/ToastService";

export default class extends Page {
  pageReady() {
    const button = this.el?.querySelector('.modal-components-toast-button') as HTMLElement;
    if (!button) {
      return;
    }

    button.addEventListener('click', () => {
      const title = button.getAttribute('data-toast-title') || '';
      const message = button.getAttribute('data-toast-message') || '';
      (this.app.getServiceOrFail(ToastService) as ToastService).show({
        type: 'success',
        title,
        message,
        timeout: 4000,
      });
    });
  }
}
