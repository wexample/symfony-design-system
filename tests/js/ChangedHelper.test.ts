import { test } from 'node:test';
import assert from 'node:assert/strict';
import { changedWay } from '../../assets/js/Helper/ChangedHelper.ts';

// A figure goes up or down; a word, or a figure that did not move, has no way.
test('the way a value went', () => {
  assert.equal(changedWay('10', '12'), 'up');
  assert.equal(changedWay('5', '4.5'), 'down');
  assert.equal(changedWay('-1', '-3'), 'down');
  assert.equal(changedWay('b', 'c'), null);
  assert.equal(changedWay('', '3'), null);
  assert.equal(changedWay('3', ''), null);
  assert.equal(changedWay('3', '3.0'), null);
});
