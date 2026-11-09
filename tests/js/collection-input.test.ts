// A collection: its items held as the form holds them, each posted under its
// index as its entry would be alone, none posted for an empty list; what an
// item added starts from; the field adding, taking off and moving items, the
// key of each kept with it.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { formDefinitionStartValue, formDefinitionCollectionTemplate, formDefinitionData } = await import('../../assets/js/Helper/FormDefinitionHelper.ts');
const CollectionInput = (await import('../../assets/components/form/collection-input/collection-input.vue?script')).default;

const property = { type: 'collection', name: 'property', label: 'Property', entry: { type: 'textarea', name: 'property', label: 'Property' }, items: ['{"a": 1}'], template: '{"name": ""}' };
const flags = { type: 'collection', name: 'flags', label: 'Flags', entry: { type: 'switch', name: 'flags', label: 'Flags' }, items: [true, false] };
const form = { name: 'node', action: '/node', token: null, fields: [property, flags] };

const posted = (data: FormData) => [...data.entries()].map(([key, value]) => [key, String(value)]);

test('a collection is held as its items, each as its entry holds one', () => {
  assert.deepEqual(formDefinitionStartValue(property), ['{"a": 1}']);
  assert.deepEqual(formDefinitionStartValue(flags), [true, false]);
  assert.deepEqual(formDefinitionStartValue(property, ['x', 'y']), ['x', 'y']);
});

test('an item added starts from the example, or from its entry empty', () => {
  assert.equal(formDefinitionCollectionTemplate(property), '{"name": ""}');
  assert.equal(formDefinitionCollectionTemplate(flags), false);
  assert.equal(formDefinitionCollectionTemplate({ ...property, template: undefined }), '');
});

test('each item is posted under its index as its entry would be; none for an empty list', () => {
  assert.deepEqual(posted(formDefinitionData(form, { property: ['b', 'a'], flags: [false, true] })), [
    ['node[property][0]', 'b'],
    ['node[property][1]', 'a'],
    ['node[flags][1]', '1']
  ]);
  assert.deepEqual(posted(formDefinitionData(form, { property: [], flags: [] })), []);
});

test('the field adds from its example, takes off and moves, each item keeping its key', () => {
  const { vm, emitted } = vueInstance(CollectionInput, { modelValue: ['a', 'b'], template: { name: '' }, name: 'node[property]' });
  const [keyA, keyB] = vm.keys;

  vm.add();
  assert.deepEqual(emitted.at(-1), ['update:modelValue', ['a', 'b', { name: '' }]]);
  // A copy, not the example itself.
  assert.notEqual(emitted.at(-1)[1][2], vm.template);

  vm.modelValue = ['a', 'b'];
  vm.keys = [keyA, keyB];
  vm.move(0, 'down');
  assert.deepEqual(emitted.at(-1), ['update:modelValue', ['b', 'a']]);
  assert.deepEqual(vm.keys, [keyB, keyA]);

  const count = emitted.length;
  vm.move(0, 'up');
  assert.equal(emitted.length, count);

  vm.remove(0);
  assert.deepEqual(emitted.at(-1), ['update:modelValue', ['b']]);
  assert.equal(vm.itemName(1), 'node[property][1]');
});
