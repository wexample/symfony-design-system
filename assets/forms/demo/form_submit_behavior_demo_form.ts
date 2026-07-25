import Form from '@wexample/symfony-loader/js/Class/Form';
import ToastService from '@wexample/symfony-loader/js/Services/ToastService';
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
    form: HTMLFormElement,
    _formData: FormData,
    _submitter: HTMLInputElement | HTMLButtonElement | null
  ): boolean {
    const behaviorSelect = form.querySelector(
      'select[name$="[behavior]"]'
    ) as HTMLSelectElement | null;

    if (behaviorSelect.value === 'js') {
      const toastService = this.app.getServiceOrFail(ToastService) as ToastService;
      toastService.show({
        type: 'info',
        sticky: true,
        title: this['trans']('@form::toast.js_action.title'),
        message: this['trans']('@form::toast.js_action.message'),
        actions: {
          reactivate: () => {
            this.trigger('loading:end', { source: this });
          },
          dismiss: () => {},
        },
      });

      return false;
    }
  }
}
