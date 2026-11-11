// What lets node run the assets as they are written for the bundler: an import
// without its `.ts` finds it, a vue file is its script, and the few packages
// of the stack the components import are stood in for by `stubs/`. Only the
// component asked for with `?script` runs; the ones it pulls in are left
// empty — a test wanting the one a component extends imports it too.
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

const STUBS = {
  '@wexample/js-api/Helper/Assistance': './stubs/Assistance.mjs',
  '@wexample/symfony-loader/js/Class/App': './stubs/App.mjs',
  '@wexample/js-helpers/Helper/Bytes': './stubs/Bytes.mjs',
  '@wexample/js-helpers/Helper/Keyboard': './stubs/Keyboard.mjs',
  '@wexample/js-api-entity/Helper/ApiEntityCollectionHelper': './stubs/ApiEntityCollectionHelper.mjs',
  '@wexample/symfony-loader/js/Services/ApiService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Services/EntityService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Services/EventsService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Services/LiveUpdatesService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Services/AdaptiveService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Services/LocaleService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Services/IconService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Class/Component': './stubs/Component.mjs',
  '@wexample/symfony-loader/js/Services/KeyboardService': './stubs/Service.mjs',
  '@wexample/symfony-design-system/js/Services/OverlayService': './stubs/Service.mjs',
  '@wexample/symfony-loader/js/Constants/FormEvents': './stubs/FormEvents.mjs',
  '@wexample/js-helpers/Helper/ElementSize': './stubs/ElementSize.mjs',
  'markdown-it': './stubs/MarkdownIt.mjs',
  '@wexample/symfony-loader/js/Services/ModalService': './stubs/ServiceModalService.mjs',
  '@wexample/symfony-loader/js/Services/PanelService': './stubs/ServicePanelService.mjs',
  '@wexample/symfony-loader/js/Services/EmbedService': './stubs/ServiceEmbedService.mjs',
  '@wexample/symfony-loader/js/Services/DockService': './stubs/ServiceDockService.mjs',
  '@wexample/symfony-loader/js/Services/ComponentsService': './stubs/ServiceComponentsService.mjs',
};

// `?whole`: the component and every vue it pulls in run their scripts — for
// a test reading what a component inherits as the component itself reads it.

// The package naming itself, as a component imports a sibling by it.
const SELF = '@wexample/symfony-design-system/';

export async function resolve(specifier, context, next) {
  if (STUBS[specifier]) {
    return next(new URL(STUBS[specifier], import.meta.url).href, context);
  }

  if (specifier.startsWith(SELF)) {
    const path = specifier.slice(SELF.length);
    specifier = new URL(`../../assets/${/\.[a-z]+$/.test(path) ? path : `${path}.ts`}`, import.meta.url).href;
  }

  // Under `?whole`, a vue pulled in runs its script too: the query follows.
  if (context.parentURL?.includes('?whole') && specifier.endsWith('.vue')) {
    const resolved = await next(specifier, context);

    return { ...resolved, url: `${resolved.url}?whole` };
  }

  if (/^\.\.?\//.test(specifier) && !/\.[a-z]+(\?.*)?$/.test(specifier)) {
    return next(`${specifier}.ts`, context);
  }

  return next(specifier, context);
}

export async function load(url, context, next) {
  const { pathname, search } = new URL(url);

  if (!pathname.endsWith('.vue')) {
    return next(url, context);
  }

  const source = search === '?script' || search === '?whole'
    ? (await readFile(fileURLToPath(`file://${pathname}`), 'utf8')).match(/<script>([\s\S]*)<\/script>/)[1]
    : 'export default {};';

  return { format: 'module', source, shortCircuit: true };
}
