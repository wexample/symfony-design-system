// What lets node run the assets as they are written for the bundler: an import
// without its `.ts` finds it, and a vue file is its script. Only the component
// asked for with `?script` runs; the ones it pulls in are left empty.
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

export async function resolve(specifier, context, next) {
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
