// A unit beside the value: named to assistive technologies through the
// field's aria-describedby, and nothing of it in what the field emits.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const FormField = (await import('../../assets/components/_abstract/form-field/form-field.vue?script')).default;
const TextInput = (await import('../../assets/components/form/text-input/text-input.vue?script')).default;

test('no word beside the value, no frame and nothing described', () => {
  const { vm } = vueInstance(TextInput, { id: 'weight' }, [FormField]);

  assert.equal(vm.hasAddon, false);
  assert.equal(vm.addonDescribedBy, null);
});

test('the words beside the value describe the field', () => {
  assert.equal(vueInstance(TextInput, { id: 'weight', suffix: 'kg' }, [FormField]).vm.addonDescribedBy, 'weight-suffix');
  assert.equal(vueInstance(TextInput, { id: 'price', prefix: '€', suffix: 'HT' }, [FormField]).vm.addonDescribedBy, 'price-prefix price-suffix');
});

test('what the field emits is the value alone', () => {
  const { vm, emitted } = vueInstance(TextInput, { id: 'weight', suffix: 'kg' }, [FormField]);

  vm.onInput({ target: { value: '72.5' } });

  assert.deepEqual(emitted, [['update:modelValue', '72.5']]);
});
