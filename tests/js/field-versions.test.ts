// The versions of a text field: what an address answers, shaped
// (FieldVersionsHelper); where they are read for the entity sent
// (FormDefinitionHelper); and the bar's own rule — a version read, the draft
// kept aside and given back, a version taken back as the draft.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { fieldVersionsNormalize } = await import('../../assets/js/Helper/FieldVersionsHelper.ts');
const { formDefinitionVersionsUrl } = await import('../../assets/js/Helper/FormDefinitionHelper.ts');
const FieldVersions = (await import('../../assets/components/form/field-versions/field-versions.vue?script')).default;

test('versions are read as a list or under data, each with its words and content', () => {
  const versions = fieldVersionsNormalize({ data: [{ label: '2026-11-05 15:57 · weeger', content: 'a' }, { content: 'b' }, null, 'x'] });

  assert.deepEqual(versions.map((version) => [version.label, version.content]), [['2026-11-05 15:57 · weeger', 'a'], ['—', 'b']]);
  assert.deepEqual(fieldVersionsNormalize({ ok: true }), []);
  assert.deepEqual(fieldVersionsNormalize({ type: 'success', data: { items: [{ label: 'v1', content: 'a' }] } }).map((version) => version.label), ['v1']);
});

test('the address is the entity\'s: none while the entity is not known', () => {
  const field = { type: 'textarea', name: 'content', versionsUrl: '/_api/template/versions/__id__' };

  assert.equal(formDefinitionVersionsUrl(field, 'u1'), '/_api/template/versions/u1');
  assert.equal(formDefinitionVersionsUrl(field, null), null);
  assert.equal(formDefinitionVersionsUrl({ ...field, versionsUrl: '/versions' }, null), '/versions');
  assert.equal(formDefinitionVersionsUrl({ type: 'textarea', name: 'notes' }, 'u1'), null);
});

test('a version read keeps the draft aside and gives it back; taken back, it is the draft', () => {
  const { vm, emitted } = vueInstance(FieldVersions, { url: '/versions', modelValue: 'typed' });
  vm.versions = [{ label: 'v1', content: 'older' }];
  vm.initial = 'saved';

  assert.equal(vm.draftLabel, 'draft');
  assert.equal(vm.choices.length, 2);

  vm.choose('0');
  assert.deepEqual(emitted.at(-1), ['update:modelValue', 'older']);
  assert.equal(vm.draft, 'typed');

  vm.choose('draft');
  assert.deepEqual(emitted.at(-1), ['update:modelValue', 'typed']);
  assert.equal(vm.kept, null);

  vm.choose('0');
  vm.take();
  assert.deepEqual(emitted.at(-1), ['update:modelValue', 'older']);
  assert.equal(vm.chosen, 'draft');
  assert.equal(vm.version, null);
});
