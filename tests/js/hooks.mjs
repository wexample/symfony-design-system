// What lets node run the assets as they are written for the bundler: an import
// without its `.ts` finds it, a vue file is its script, and the few packages
// of the stack the components import are stood in for by `stubs/`. Only the
// component asked for with `?script` runs; the ones it pulls in are left
// empty — a test wanting the one a component extends imports it too.
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

const STUBS = {
  '@wexample/js-api/Helper/Assistance': './stubs/Assistance.mjs',
};

export async function resolve(specifier, context, next) {
  if (STUBS[specifier]) {
    return next(new URL(STUBS[specifier], import.meta.url).href, context);
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

  const source = search === '?script'
    ? (await readFile(fileURLToPath(`file://${pathname}`), 'utf8')).match(/<script>([\s\S]*)<\/script>/)[1]
    : 'export default {};';

  return { format: 'module', source, shortCircuit: true };
}
