import AppService from '@wexample/symfony-loader/js/Class/AppService';
import ToastService from '@wexample/symfony-loader/js/Services/ToastService';

/**
 * A form marked `data-request="api"` is sent without leaving the page: posted
 * to its address as it would have been — its fields, its token —, asking for
 * json, and what the api answers (`{ type, message }`) is said in a toast. For
 * an action whose result the page does not show: a mail sent again.
 *
 * Asked first when it carries a confirmation: the confirm service holds the
 * submission and sends it again once answered, which is when this one takes it.
 */
export default class ApiRequestService extends AppService {
  public static serviceName: string = 'apiRequest';

  registerHooks() {
    return {
      app: {
        hookInit: () => {
          document.addEventListener('submit', this.onSubmit);
        },
      },
    };
  }

  private onSubmit = async (event: SubmitEvent): Promise<void> => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.dataset.request !== 'api' || event.defaultPrevented) {
      return;
    }

    event.preventDefault();

    const buttons = Array.from(form.querySelectorAll<HTMLButtonElement>('button'));
    buttons.forEach((button) => { button.disabled = true; });

    let type: 'success' | 'error' = 'error';
    let message = '';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body: new FormData(form),
      });
      const body = await response.json().catch(() => ({}));

      type = response.ok ? 'success' : 'error';
      message = body?.message ?? response.statusText;
    } catch (error) {
      message = String((error as Error)?.message ?? error);
    } finally {
      buttons.forEach((button) => { button.disabled = false; });
    }

    (this.app.getServiceOrFail(ToastService) as ToastService).show({ type, message });
  };
}
