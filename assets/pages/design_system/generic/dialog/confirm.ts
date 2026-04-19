import Page from '@wexample/symfony-loader/js/Class/Page';
import ConfirmService from "@wexample/symfony-loader/js/Services/ConfirmService";
import ToastService from "@wexample/symfony-loader/js/Services/ToastService";

export default class extends Page {
  pageReady() {
    const confirmService = this.app.getServiceOrFail(ConfirmService) as any;
    const toastService = this.app.getServiceOrFail(ToastService) as any;

    const attachConfirmDemo = (
      selector: string,
      defaultPreset: string,
      mode: 'confirm' | 'confirmToast',
    ) => {
      const button = this.el?.querySelector(selector) as HTMLElement;
      if (!button) {
        return;
      }
      button.addEventListener('click', async () => {
        const title = button.getAttribute('data-confirm-title') || '';
        const message = button.getAttribute('data-confirm-message') || '';
        const preset = button.getAttribute('data-confirm-preset') || defaultPreset;
        const resultTitle = button.getAttribute('data-result-title') || '';
        const resultPrefix = button.getAttribute('data-result-prefix') || '';
        const result = await confirmService[mode]({
          title,
          message,
          preset
        });
        toastService.show({
          type: 'info',
          title: resultTitle,
          message: `${resultPrefix} ${result}`,
          timeout: 3000,
        });
      });
    };

    attachConfirmDemo('.confirm-demo-button', 'yes_no', 'confirm');
    attachConfirmDemo('.confirm-demo-medium-button', 'yes_no', 'confirm');
    attachConfirmDemo('.confirm-demo-long-button', 'yes_no', 'confirm');
    attachConfirmDemo('.confirm-demo-toast-button', 'ok_cancel', 'confirmToast');
    attachConfirmDemo('.confirm-demo-toast-medium-button', 'ok_cancel', 'confirmToast');
    attachConfirmDemo('.confirm-demo-toast-long-button', 'ok_cancel', 'confirmToast');

    const confirmCustomButton = this.el?.querySelector('.confirm-demo-custom-button') as HTMLElement;
    if (confirmCustomButton) {
      const openCustomConfirm = async () => {
        const title = confirmCustomButton.getAttribute('data-confirm-title') || '';
        const message = confirmCustomButton.getAttribute('data-confirm-message') || '';
        const actionNew = confirmCustomButton.getAttribute('data-action-new') || '';
        const actionClose = confirmCustomButton.getAttribute('data-action-close') || '';
        const actionCancel = confirmCustomButton.getAttribute('data-action-cancel') || '';
        const result = await confirmService.confirm({
          title,
          message,
          actions: [
            { key: 'n', value: 'new', label: actionNew, role: 'primary', keepOpen: true },
            { key: 'c', value: 'close_all', label: actionClose, role: 'destructive' },
            { key: 'x', value: 'cancel', label: actionCancel, role: 'secondary' },
          ],
        });

        if (result === 'new') {
          await openCustomConfirm();
          return;
        }

        if (result === 'close_all') {
          await confirmService.closeAll();
          return;
        }
      };

      confirmCustomButton.addEventListener('click', () => {
        openCustomConfirm();
      });
    }
  }
}
