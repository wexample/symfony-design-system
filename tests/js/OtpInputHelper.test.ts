// A code field written in cell by cell: what is typed replaces, what is erased
// leaves its place, and a code given whole lands whole.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
  otpInputComplete,
  otpInputErase,
  otpInputFrom,
  otpInputMove,
  otpInputValue,
  otpInputWrite,
} from '../../assets/js/Helper/OtpInputHelper.ts';

test('a code given whole fills the cells, the next one active', () => {
  const state = otpInputFrom('123', 6);

  assert.deepEqual(state.chars, ['1', '2', '3', '', '', '']);
  assert.equal(state.active, 3);
  assert.equal(otpInputFrom('123456', 6).active, 5);
});

test('typing in a cell replaces it, nothing moves aside', () => {
  const state = otpInputWrite(otpInputMove(otpInputFrom('123456', 6), 2), '9');

  assert.equal(otpInputValue(state), '129456');
  assert.equal(state.active, 3);
});

test('typing on goes from cell to cell and stops at the last', () => {
  const state = otpInputWrite(otpInputFrom('', 6), '12345678');

  assert.equal(otpInputValue(state), '123456');
  assert.equal(state.active, 5);
  assert.ok(otpInputComplete(state));
});

test('erasing a cell keeps its place, the code no longer complete', () => {
  const state = otpInputErase(otpInputMove(otpInputFrom('123456', 6), 2), true);

  assert.deepEqual(state.chars, ['1', '2', '', '4', '5', '6']);
  assert.equal(state.active, 2);
  assert.ok(!otpInputComplete(state));
});

test('backspace from an empty cell erases the one before and goes back to it', () => {
  const state = otpInputErase(otpInputFrom('123', 6), true);

  assert.deepEqual(state.chars, ['1', '2', '', '', '', '']);
  assert.equal(state.active, 2);
});

test('delete from an empty cell erases nothing', () => {
  const state = otpInputErase(otpInputFrom('123', 6), false);

  assert.equal(otpInputValue(state), '123');
  assert.equal(state.active, 3);
});

test('any cell can be reached, none past the ends', () => {
  const state = otpInputFrom('12', 6);

  assert.equal(otpInputMove(state, 4).active, 4);
  assert.equal(otpInputMove(state, -1).active, 0);
  assert.equal(otpInputMove(state, 9).active, 5);
});
