import Page from '@wexample/symfony-loader/js/Class/Page';
import ToastService from "@wexample/symfony-loader/js/Services/ToastService";

export default class extends Page {
  pageReady() {
    const attachToastButton = (
      selector: string,
      options: { timeout?: number; stackId?: string; allowHtml?: boolean; actions?: Record<string, () => void> }
    ) => {
      const button = this.el?.querySelector(selector) as HTMLElement;
      if (!button) {
        return;
      }
      button.addEventListener('click', () => {
        const title = button.getAttribute('data-toast-title') || '';
        const message = button.getAttribute('data-toast-message') || '';
        (this.app.getServiceOrFail(ToastService) as any).show({
          type: 'info',
          title,
          message,
          ...(options.allowHtml ? { allowHtml: true } : {}),
          ...(options.actions ? { actions: options.actions } : {}),
          ...(options.timeout ? { timeout: options.timeout } : {}),
          ...(options.stackId ? { stackId: options.stackId } : {})
        });
      });
    };

    attachToastButton('.toast-demo-button', {
      timeout: 4000,
      stackId: 'default',
      allowHtml: true,
      actions: {
        demo: () => {
          (this.app.getServiceOrFail(ToastService) as any).show({
            type: 'success',
            title: 'Action link',
            message: 'Demo action executed.',
            timeout: 3000
          });
        }
      }
    });
    attachToastButton('.toast-demo-button-tl', { timeout: 4000, stackId: 'demo-tl' });
    attachToastButton('.toast-demo-button-medium', { timeout: 6000 });
    attachToastButton('.toast-demo-button-long', { timeout: 8000 });

    const popupButtons = Array.from(this.el?.querySelectorAll('.toast-demo-popup') || []) as HTMLElement[];
    popupButtons.forEach((popupButton) => {
      popupButton.addEventListener('click', () => {
        const type = popupButton.getAttribute('data-toast-type') || 'info';
        (this.app.getServiceOrFail(ToastService) as any).show({
          type,
          title: `${type.charAt(0).toUpperCase()}${type.slice(1)} toast`,
          message: 'Hello from the toast demo popup.',
          timeout: 4000
        });
      });
    });
  }
}
