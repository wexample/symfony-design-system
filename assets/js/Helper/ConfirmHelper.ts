import type App from '@wexample/symfony-loader/js/Class/App';
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';
import type ConfirmService from '../Services/ConfirmService';

// The words of the question when the element gives none.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.confirm.';

// The confirmation an element carries (`data-confirm-title`,
// `data-confirm-message`, `data-confirm-accept`, `data-confirm-cancel`, the
// last two translated where it was drawn), asked with the system's
// confirm where the app has one, the browser's otherwise. True when accepted,
// and when the element asks nothing.
export async function confirmAskFor(app: App, el: HTMLElement, cancelLabel?: string): Promise<boolean> {
  const { confirmTitle, confirmMessage, confirmAccept, confirmCancel } = el.dataset;

  if (!confirmMessage) {
    return true;
  }

  const confirmService = (app.services as Record<string, unknown>).confirm as ConfirmService | undefined;

  if (!confirmService) {
    return window.confirm(confirmMessage);
  }

  const locale = app.getServiceOrFail(LocaleService) as LocaleService;
  const result = await confirmService.confirm({
    title: confirmTitle || undefined,
    message: confirmMessage,
    actions: [
      { key: 'y', value: 'ok', label: confirmAccept || locale.trans(`${DOMAIN}accept`), role: 'primary' },
      { key: 'n', value: 'cancel', label: confirmCancel || cancelLabel || locale.trans(`${DOMAIN}cancel`), role: 'secondary' },
    ],
  });

  return result === 'ok';
}
