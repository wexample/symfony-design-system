// The kinds saying yes or no, and a number, as dynamic-form holds and posts
// them (FormDefinitionHelper): a toggle a boolean, posted as a drawn checkbox
// is — its value while on, nothing while off —; a number as its text.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';

register('./hooks.mjs', import.meta.url);

const { formDefinitionChecked, formDefinitionData } = await import('../../assets/js/Helper/FormDefinitionHelper.ts');

const form = {
  name: 'slot_configs',
  action: '/slot/__id__',
  token: null,
  fields: [
    { type: 'switch', name: 'streaming', label: 'Streaming', checked: true, value: '1' },
    { type: 'checkbox', name: 'aggregate', label: 'Aggregate', checked: false, value: '1' },
    { type: 'number', name: 'timeout', label: 'Timeout', value: '30', min: 0, step: 5 }
  ]
};

test('a yes or no is read from a boolean or the words a record holds it as', () => {
  assert.deepEqual([true, 1, '1', 'true', 'on', 'yes'].map(formDefinitionChecked), [true, true, true, true, true, true]);
  assert.deepEqual([false, 0, null, undefined, '', '0', 'false', 'off', 'no'].map(formDefinitionChecked), [false, false, false, false, false, false, false, false, false]);
});

test('a toggle posts its value while on and nothing while off; a number its text', () => {
  const asForm = [...formDefinitionData(form).entries()];
  assert.deepEqual(asForm, [['slot_configs[streaming]', '1'], ['slot_configs[timeout]', '30']]);

  const changed = [...formDefinitionData(form, { streaming: false, aggregate: true, timeout: 45 }).entries()];
  assert.deepEqual(changed, [['slot_configs[aggregate]', '1'], ['slot_configs[timeout]', '45']]);
});
