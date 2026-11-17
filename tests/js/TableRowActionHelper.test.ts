// The place of a row's actions by their `rel`, written by
// src/Helper/TableRowActionHelper.php.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { sortActionsByRel } from '../../assets/js/Helper/TableRowActionHelper.ts';

test('open, edit, the others, delete', () => {
  const sorted = sortActionsByRel([
    { label: 'Delete', rel: 'delete' },
    { label: 'Test' },
    { label: 'Edit', rel: 'edit' },
    { label: 'Download', rel: null },
    { label: 'Open', rel: 'open' },
  ]);

  assert.deepEqual(sorted.map((action) => action.label), ['Open', 'Edit', 'Test', 'Download', 'Delete']);
});

test('the given list is left as it is', () => {
  const actions = [{ label: 'Delete', rel: 'delete' }, { label: 'Open', rel: 'open' }];

  sortActionsByRel(actions);

  assert.equal(actions[0].label, 'Delete');
});
