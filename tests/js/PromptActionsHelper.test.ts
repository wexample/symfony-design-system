// Which answer of a question Enter takes and the focus starts on.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { promptActionCancel, promptActionDefault } from '../../assets/js/Helper/PromptActionsHelper.ts';

const deactivate = [
  { key: 'y', value: 'deactivate', label: 'Deactivate', role: 'destructive' as const },
  { key: 'n', value: 'cancel', label: 'Cancel', role: 'secondary' as const },
];
const save = [
  { key: 'y', value: 'ok', label: 'Ok', role: 'primary' as const },
  { key: 'n', value: 'cancel', label: 'Cancel', role: 'secondary' as const },
];

test('beside a destructive answer, the default is the one that backs out', () => {
  assert.equal(promptActionDefault(deactivate)?.value, 'cancel');
});

test('otherwise, the primary one', () => {
  assert.equal(promptActionDefault(save)?.value, 'ok');
});

test('a named default wins, whatever the roles', () => {
  assert.equal(promptActionDefault(deactivate, 'deactivate')?.value, 'deactivate');
  assert.equal(promptActionDefault(save, 'nowhere')?.value, 'ok');
});

test('the answer that backs out', () => {
  assert.equal(promptActionCancel(deactivate)?.value, 'cancel');
  assert.equal(promptActionCancel([{ key: 'x', value: 'stop', label: 'Stop', role: 'secondary' }])?.value, 'stop');
  assert.equal(promptActionCancel([]), null);
});
