import App from '@wexample/symfony-loader/js/Class/App';
import ModalService from '@wexample/symfony-loader/js/Services/ModalService';
import PanelService from '@wexample/symfony-loader/js/Services/PanelService';
import EmbedService from '@wexample/symfony-loader/js/Services/EmbedService';

export const TARGET_MODAL = 'modal';
export const TARGET_PANEL = 'panel';

// The two overlays keep their place in the location hash, so a reload finds them
// open again. An embed does not: it belongs to the page that placed it, and that
// page is what the address already names.
export const TARGET_HASH_KEYS: { [target: string]: [string, string, string] } = {
  [TARGET_MODAL]: ['modal', 'modal.opts', 'modal.page'],
  [TARGET_PANEL]: ['panel', 'panel.opts', 'panel.page'],
};

// Where a link loads its page when it does not replace the current one. The two
// overlays are named by their kind, a page holding at most one of each; anything
// else is the name of an embed the page has placed.
export function loadIntoTarget(
  app: App,
  target: string,
  href: string,
  options: Record<string, any> = {}
): Promise<any> {
  if (TARGET_MODAL === target) {
    return (app.getServiceOrFail(ModalService) as ModalService).get(href, options);
  }

  if (TARGET_PANEL === target) {
    return (app.getServiceOrFail(PanelService) as PanelService).get(href, options);
  }

  return (app.getServiceOrFail(EmbedService) as EmbedService).load(target, href, options);
}
