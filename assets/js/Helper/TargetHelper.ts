import App from '@wexample/symfony-loader/js/Class/App';
import ModalService from '@wexample/symfony-loader/js/Services/ModalService';
import PanelService from '@wexample/symfony-loader/js/Services/PanelService';
import EmbedService from '@wexample/symfony-loader/js/Services/EmbedService';
import DockService from '@wexample/symfony-loader/js/Services/DockService';
import ComponentsService from '@wexample/symfony-loader/js/Services/ComponentsService';

export const TARGET_MODAL = 'modal';
export const TARGET_PANEL = 'panel';
// A window docked at the foot of the screen, beside the page and not over it.
export const TARGET_DOCK = 'dock';

// The two overlays keep their place in the location hash, so a reload finds them
// open again. An embed does not: it belongs to the page that placed it, and that
// page is what the address already names.
export const TARGET_HASH_KEYS: { [target: string]: [string, string, string] } = {
  [TARGET_MODAL]: ['modal', 'modal.opts', 'modal.page'],
  [TARGET_PANEL]: ['panel', 'panel.opts', 'panel.page'],
};

// Where a link opens when the page holding it is embedded — drawn in a panel,
// a modal or an embed — and the window is not to be left, or when the embed
// it names is not on this page: a page opened in a panel has none of the
// embeds its full-page self places. A panel by default, over whatever is open:
// a panel in a panel reads, and is less jarring than a panel then a modal.
// Said link by link, or by any element around it, with `data-target-embedded`
// (`modal`, `panel`, an embed's name), or `targetEmbedded` in JS; `none` keeps
// what the link would do otherwise.
export const TARGET_EMBEDDED_DEFAULT = TARGET_PANEL;
export const TARGET_EMBEDDED_NONE = 'none';

export function targetEmbeddedOf(el: Element | null, explicit: string | null = null): string {
  return explicit
    || el?.closest('[data-target-embedded]')?.getAttribute('data-target-embedded')
    || TARGET_EMBEDDED_DEFAULT;
}

// Where a link loads its page when it does not replace the current one. The two
// overlays are named by their kind; anything else is the name of an embed the
// page has placed — or, that embed missing, where the link's `targetEmbedded`
// option says, a panel unless told.
export function loadIntoTarget(
  app: App,
  target: string,
  href: string,
  options: Record<string, any> = {}
): Promise<any> {
  const { targetEmbedded, ...requestOptions } = options;

  if (TARGET_MODAL === target) {
    return (app.getServiceOrFail(ModalService) as ModalService).get(href, requestOptions);
  }

  if (TARGET_PANEL === target) {
    return (app.getServiceOrFail(PanelService) as PanelService).get(href, requestOptions);
  }

  if (TARGET_DOCK === target) {
    return (app.getServiceOrFail(DockService) as DockService).get(href, requestOptions);
  }

  const embeds = app.getServiceOrFail(EmbedService) as EmbedService;

  if (embeds.has(target)) {
    return embeds.load(target, href, requestOptions);
  }

  const fallback = targetEmbedded || TARGET_EMBEDDED_DEFAULT;

  // Nowhere else to go, or nowhere new: the page itself, as a link would.
  if (TARGET_EMBEDDED_NONE === fallback || fallback === target) {
    window.location.assign(href);

    return Promise.resolve();
  }

  return loadIntoTarget(app, fallback, href, { ...requestOptions, targetEmbedded: TARGET_EMBEDDED_NONE });
}

// A plain link of an embedded page, about to leave for a whole new page and
// take down what lies under the panel or the modal: opened where
// `target-embedded` says instead. Called by the managers drawing such pages;
// says whether it took the click.
export function targetEmbeddedClick(app: App, event: MouseEvent, link: HTMLAnchorElement): boolean {
  const target = targetEmbeddedOf(link);

  if (TARGET_EMBEDDED_NONE === target) {
    return false;
  }

  event.preventDefault();
  void loadIntoTarget(app, target, link.getAttribute('href') ?? link.href, { targetEmbedded: TARGET_EMBEDDED_NONE });

  return true;
}

export type TargetContent = {
  // The header's title.
  title?: string;
  // The body: markup, or an element moved in as it is — a list a script keeps
  // up to date.
  body: string | HTMLElement;
  // The foot, kept in view whatever the body scrolls: markup.
  actions?: string;
};

// The shell a page loaded in a modal, a panel or a dock is drawn in, for what
// a script shows there itself. Twin of the loader's
// bases/json/_overlay-shell-header.html.twig and of the body and foot the
// json bases draw: a change to either is a change to both.
function targetShellHtml(app: App, type: string, content: TargetContent): string {
  const escape = (value: string) => value.replace(/[&<>"']/g, (char) => `&#${char.charCodeAt(0)};`);
  const title = content.title ? `<h2 class="header-title">${escape(content.title)}</h2>` : '<div class="header-title"></div>';

  return `<div class="layout-${type}">`
    + `<div class="${type}-header">${title}<div class="${type}-close"><a class="action-icon" href="#">`
    + `${(app.getServiceOrFail('icon') as any).icon('ph:bold/x')}</a></div></div>`
    + `<div class="${type}-loading"><div class="${type}-loading-bar"></div></div>`
    + `<div class="${type}-body stack--vertical">${typeof content.body === 'string' ? content.body : ''}</div>`
    + (content.actions ? `<div class="${type}-actions page-actions">${content.actions}</div>` : '')
    + '</div>';
}

/**
 * What a script shows in a modal, a panel or a dock itself, rather than a page
 * the server renders: the same window, its shell drawn here. Answers the
 * window, to close it (`close()`) or to fold a dock (`setCollapsed()`).
 * Its template must be on the page (`component_frontend()`, which the
 * dashboard layout does for the three).
 */
export async function showInTarget(app: App, target: string, content: TargetContent): Promise<any> {
  if (![TARGET_MODAL, TARGET_PANEL, TARGET_DOCK].includes(target)) {
    throw new Error(`Nothing can be shown in "${target}": a modal, a panel or a dock.`);
  }

  const created = await (app.getServiceOrFail(ComponentsService) as ComponentsService).createComponentFromTemplate(
    `@WexampleSymfonyDesignSystemBundle/components/${target}`,
    {},
    app.layout
  );

  if (!created) {
    return null;
  }

  const instance: any = created.instance;
  instance.setLayoutBody(targetShellHtml(app, target, content));

  if (typeof content.body !== 'string') {
    instance.el.querySelector(`.${target}-body`)?.appendChild(content.body);
  }

  await instance.open();

  return instance;
}
