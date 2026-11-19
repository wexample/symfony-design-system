// What is attached to a field whatever its kind (FieldExtensionsHelper):
// the registry's order, an unknown name left out; an expression told by its
// pattern, posted as it is by the definition (FormDefinitionHelper); and the
// expression extension's own rule — on when the value is one, the value kept
// before given back on the way back.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { fieldExtensionsList, fieldExpressionMatches, FIELD_EXTENSIONS } = await import('../../assets/js/Helper/FieldExtensionsHelper.ts');
const { formDefinitionData } = await import('../../assets/js/Helper/FormDefinitionHelper.ts');
const Expression = (await import('../../assets/components/form/extension/expression/expression.vue?script')).default;

test('a field draws the extensions it names, in the registry\'s order, the unknown left out', () => {
  const drawn = fieldExtensionsList({ versions: { url: '/v' }, wings: { span: 2 }, expression: { language: 'jinja', pattern: '^x' } });
  assert.deepEqual(drawn.map((extension) => extension.name), ['expression', 'versions']);
  assert.equal(drawn[1].component, FIELD_EXTENSIONS.versions.component);
  assert.deepEqual(fieldExtensionsList(null), []);
});

test('an expression is told by its pattern', () => {
  assert.equal(fieldExpressionMatches('\\{[{%]', '{{ model }}'), true);
  assert.equal(fieldExpressionMatches('\\{[{%]', '12'), false);
  assert.equal(fieldExpressionMatches('[', 'x'), false);
  assert.equal(fieldExpressionMatches(null, '{{ x }}'), false);
});

test('an expression is posted as it is, whatever the kind would have posted', () => {
  const form = { name: 'slot', action: '/slot', token: null, fields: [
    { type: 'switch', name: 'streaming', label: 'Streaming', checked: false, value: '1', extensions: { expression: { language: 'jinja', pattern: '\\{[{%]' } } },
    { type: 'number', name: 'timeout', label: 'Timeout', value: '30', extensions: { expression: { language: 'jinja', pattern: '\\{[{%]' } } }
  ] };
  assert.deepEqual([...formDefinitionData(form, { streaming: '{{ model.streams }}', timeout: '{{ model.timeout }}' }).entries()], [['slot[streaming]', '{{ model.streams }}'], ['slot[timeout]', '{{ model.timeout }}']]);
  assert.deepEqual([...formDefinitionData(form, { streaming: true, timeout: 45 }).entries()], [['slot[streaming]', '1'], ['slot[timeout]', '45']]);
});

test('the expression extension starts on when the value is one, and gives the value back on the way back', () => {
  const { vm, emitted } = vueInstance(Expression, { pattern: '\\{[{%]', modelValue: '30' });
  vm.held = vm.read();
  assert.equal(vm.on, false);

  vm.switchOn();
  assert.deepEqual([vm.on, vm.kept, emitted.at(-1)], [true, '30', ['control', true]]);
  vm.onEditorInput('{{ model.timeout }}');
  assert.deepEqual(emitted.at(-1), ['update:modelValue', '{{ model.timeout }}']);

  vm.switchOff();
  assert.deepEqual([vm.on, emitted.at(-1)], [false, ['update:modelValue', '30']]);
});

// An extension holding a value of its own (`value` in its definition):
// whether the field enters a test matrix, sent beside the field under
// `<field>__<extension>`, the field's own value untouched.
const Toggle = (await import('../../assets/components/form/extension/toggle/toggle.vue?script')).default;

test('a value an extension holds is posted beside its field, a yes or no as 1 or 0', () => {
  const form = { name: 'slot', action: '/slot', token: null, fields: [
    { type: 'number', name: 'temperature', label: 'Temperature', value: '0.7', extensions: { toggle: { value: true, icon: 'ph:bold/flask', label: 'In the test matrix' } } },
    { type: 'text', name: 'model', label: 'Model', value: 'gpt', extensions: { versions: { url: '/v' } } }
  ] };
  // As the field gives it, then switched off.
  assert.deepEqual([...formDefinitionData(form).entries()], [['slot[temperature]', '0.7'], ['slot[temperature__toggle]', '1'], ['slot[model]', 'gpt']]);
  assert.deepEqual([...formDefinitionData(form, { temperature: '0.9', temperature__toggle: false }).entries()].slice(0, 2), [['slot[temperature]', '0.9'], ['slot[temperature__toggle]', '0']]);
});

test('the toggle extension switches, where the form holding it reads it', () => {
  const context = { extensionValues: { temperature__toggle: false } };
  const { vm, emitted } = vueInstance(Toggle, { value: false, icon: 'ph:bold/flask', label: 'In the test matrix', fieldName: 'slot[temperature]' });
  vm.dynamicFormContext = context;

  assert.equal(vm.contextKey, 'temperature__toggle');
  vm.toggle();
  assert.deepEqual([vm.on, context.extensionValues.temperature__toggle, emitted.at(-1)], [true, true, ['update:value', true]]);
  vm.toggle();
  assert.deepEqual([vm.on, context.extensionValues.temperature__toggle], [false, false]);
});
