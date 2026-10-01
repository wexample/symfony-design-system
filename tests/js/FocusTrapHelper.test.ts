// Tab inside an open dialog: round from the last element to the first and
// back, never out into the page behind it.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { focusTrapNext } from '../../assets/js/Helper/FocusTrapHelper.ts';

const [a, b, c] = [{ tabIndex: 0, id: 'a' }, { tabIndex: 0, id: 'b' }, { tabIndex: 0, id: 'c' }];
const all = [a, b, c];

test('inside, the browser moves on by itself', () => {
  assert.equal(focusTrapNext(all, a, false), null);
  assert.equal(focusTrapNext(all, c, true), null);
});

test('past the last, back to the first; before the first, on to the last', () => {
  assert.equal(focusTrapNext(all, c, false), a);
  assert.equal(focusTrapNext(all, a, true), c);
});

test('from the dialog itself or from outside, into it', () => {
  assert.equal(focusTrapNext(all, null, false), a);
  assert.equal(focusTrapNext(all, null, true), c);
});

test('nothing to reach, nothing to move to', () => {
  assert.equal(focusTrapNext([], null, false), null);
});
