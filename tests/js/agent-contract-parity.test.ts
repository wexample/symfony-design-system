// A field's contract for agents (`<x>.ai.yml`) is one file for its two faces:
// each action it declares is run by the twig face's script and by the vue
// alike — a face may run more, never less. Read as each face reads it: the
// script instantiated, the vue's options on a bare instance, and the keys of
// what `getAgentActions()` returns.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { readdir, readFile, stat } from 'node:fs/promises';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const ROOT = new URL('../../assets/components/form/', import.meta.url);

// The actions a contract declares: the keys under `actions:`.
function declaredActions(yaml: string): string[] {
  const lines = yaml.split('\n');
  const start = lines.findIndex((line) => line === 'actions:');

  if (start < 0) {
    return [];
  }

  const actions: string[] = [];

  for (const line of lines.slice(start + 1)) {
    if (/^\S/.test(line)) {
      break;
    }

    const match = line.match(/^ {2}([a-z_]+):/);

    if (match) {
      actions.push(match[1]);
    }
  }

  return actions;
}

async function exists(url: URL): Promise<boolean> {
  return stat(url).then(() => true, () => false);
}

const components = (await readdir(ROOT, { withFileTypes: true })).filter((entry) => entry.isDirectory()).map((entry) => entry.name);

for (const name of components) {
  const contract = new URL(`${name}/${name}.ai.yml`, ROOT);

  if (!(await exists(contract))) {
    continue;
  }

  const declared = declaredActions(await readFile(contract, 'utf8'));

  test(`${name}: each face runs every action the contract declares`, async () => {
    const script = new URL(`${name}/${name}.ts`, ROOT);
    const vue = new URL(`${name}/${name}.vue`, ROOT);
    const faces: Record<string, string[]> = {};

    if (await exists(script)) {
      const Face = (await import(script.href)).default;
      faces.script = Object.keys(new Face().getAgentActions());
    }

    if (await exists(vue)) {
      const options = (await import(`${vue.href}?whole`)).default;
      const chain: any[] = [];

      for (let current = options; current; current = current.extends) {
        chain.unshift(current);
      }

      const { vm } = vueInstance(options, {}, chain.slice(0, -1));
      faces.vue = Object.keys(vm.getAgentActions());
    }

    assert.ok(Object.keys(faces).length > 0, `${name} declares a contract and has no face to run it`);

    for (const [face, actions] of Object.entries(faces)) {
      const missing = declared.filter((action) => !actions.includes(action));

      assert.deepEqual(missing, [], `${name}'s ${face} face does not run: ${missing.join(', ')}`);
    }
  });
}
