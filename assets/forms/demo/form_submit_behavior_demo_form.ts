import Form from '@wexample/symfony-loader/js/Class/Form';
import ToastService from '@wexample/symfony-loader/js/Services/ToastService';
import ConfirmService from '@wexample/symfony-loader/js/Services/ConfirmService';
import { ACTION_DEFAULT, ACTION_EMBED_STAY } from '@wexample/symfony-loader/js/Constants/FormActions';

export default class extends Form {
  protected shouldCloseEmbed(payload: any): boolean {
    return payload?.action?.type !== ACTION_EMBED_STAY;
  }

  protected handleSuccessAction(action: any): void {
    if (action?.type === ACTION_DEFAULT) {
      const toastService = this.app.getServiceOrFail(ToastService) as ToastService;
      toastService.show({
        type: 'success',
        title: this['trans']('@form::toast.success.title'),
        message: this['trans']('@form::toast.success.message'),
      });
    }
  }

  protected onBeforeSubmit(
    _event: SubmitEvent,
    _form: HTMLFormElement,
    _formData: FormData,
    submitter: HTMLInputElement | HTMLButtonElement | null
  ): boolean {
    if (submitter?.name?.endsWith('[submit_js]')) {
      this.beginSubmit(_form, submitter);

      const confirmService = this.app.getServiceOrFail(ConfirmService) as ConfirmService;
      confirmService.confirmToast({
        title: this['trans']('@form::toast.js_action.title'),
        message: this['trans']('@form::toast.js_action.message'),
        actions: [
          { key: 'r', value: 'reactivate', label: this['trans']('@form::toast.js_action.reactivate'), role: 'primary' },
          { key: 'd', value: 'dismiss', label: this['trans']('@form::toast.js_action.dismiss'), role: 'secondary' },
        ],
      }).then((response) => {
        if (response === 'reactivate') {
          this.endSubmit();
        }
      });

      return false;
    }

    return true;
  }
}
