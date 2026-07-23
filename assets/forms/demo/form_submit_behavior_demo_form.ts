import Form from '@wexample/symfony-loader/js/Class/Form';
import ToastService from '@wexample/symfony-loader/js/Services/ToastService';
import { ACTION_EMBED_STAY } from '@wexample/symfony-loader/js/Constants/FormActions';

export default class extends Form {
  protected shouldCloseEmbed(payload: any): boolean {
    return payload?.action?.type !== ACTION_EMBED_STAY;
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
        type: 'success',
        title: this['trans']('@form::toast.success.title'),
        message: this['trans']('@form::toast.success.message'),
      });

      return false;
    }
  }
}
