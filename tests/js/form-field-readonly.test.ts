// A frozen vue field: `readonly` on the base every input extends, taken by the
// control itself where HTML allows it, and turned into the value in a readonly
// field holding no name where it does not — a select, radios, a switch, a
// file, a code, an emoji. The template of that field is the base's, checked on
// the demo page; here, what each control says once frozen.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const load = async (path: string) => (await import(`../../assets/components/${path}.vue?script`)).default;
const FormField = await load('_abstract/form-field/form-field');
const TextInput = await load('form/text-input/text-input');
const field = (path: string) => load(`form/${path}/${path}`);

const options = [{ value: 'se', label: 'Sweden' }, { value: 'pt', label: 'Portugal' }];

test('a field that takes readonly itself shows no stand-in, and drops a required nobody can meet', () => {
  const { vm } = vueInstance(TextInput, { readonly: true, required: true, modelValue: 'Maya' }, [FormField]);

  assert.equal(vm.isFrozenText, false);
  assert.equal(vm.isRequired, false);
});

test('an editable field stays as it was', async () => {
  for (const [path, props] of [['select-input', { options }], ['switch-input', {}], ['text-input', { required: true }]] as const) {
    const { vm } = vueInstance(await field(path), props, [FormField]);

    assert.equal(vm.isFrozenText, false, path);
  }

  assert.equal(vueInstance(TextInput, { required: true }, [FormField]).vm.isRequired, true);
});

test('a frozen select or radio group says the label of what is chosen', async () => {
  for (const path of ['select-input', 'radio-input']) {
    const { vm } = vueInstance(await field(path), { readonly: true, options, modelValue: 'pt', translate: false }, [FormField]);

    assert.equal(vm.isFrozenText, true, path);
    assert.equal(vm.frozenText, 'Portugal', path);
  }
});

test('a frozen radio group with nothing chosen says nothing', async () => {
  const { vm } = vueInstance(await field('radio-input'), { readonly: true, options, translate: false }, [FormField]);

  assert.equal(vm.frozenText, '');
});

test('a frozen switch says its state, in the page\'s words or the default ones', async () => {
  const Switch = await field('switch-input');
  const named = { readonly: true, checkedLabel: 'Active', uncheckedLabel: 'Deactivated', translate: false };

  assert.equal(vueInstance(Switch, { ...named, modelValue: true }, [FormField]).vm.frozenText, 'Active');
  assert.equal(vueInstance(Switch, { ...named, modelValue: false }, [FormField]).vm.frozenText, 'Deactivated');
  assert.equal(vueInstance(Switch, { readonly: true, modelValue: true }, [FormField]).vm.frozenText, 'checked');
  assert.equal(vueInstance(Switch, { readonly: true, modelValue: false }, [FormField]).vm.frozenText, 'unchecked');
});

test('a frozen file field says what is held: a stored name, or the files chosen', async () => {
  const File = await field('file-input');

  assert.equal(vueInstance(File, { readonly: true, modelValue: 'passport.pdf' }, [FormField]).vm.frozenText, 'passport.pdf');
  assert.equal(
    vueInstance(File, { readonly: true, modelValue: [{ name: 'a.pdf' }, { name: 'b.png' }] }, [FormField]).vm.frozenText,
    'a.pdf, b.png'
  );
  assert.equal(vueInstance(File, { readonly: true }, [FormField]).vm.frozenText, '');
});

test('a frozen code or emoji says its value', async () => {
  assert.equal(vueInstance(await field('otp-input'), { readonly: true, modelValue: '482915' }, [FormField]).vm.frozenText, '482915');
  assert.equal(vueInstance(await field('emoji-picker'), { readonly: true, modelValue: '😀' }, [FormField]).vm.frozenText, '😀');
});
