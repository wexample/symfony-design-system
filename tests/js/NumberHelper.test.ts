// A figure in a locale, and the decimals a step asks for: what the table and
// the scale both show.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { numberFormat, numberStepDigits } from '../../assets/js/Helper/NumberHelper.ts';

test('a figure in the locale named, decimals capped', () => {
  assert.equal(numberFormat(1234.567, 2, 'en'), '1,234.57');
  assert.equal(numberFormat('-4', 0, 'en'), '-4');
  assert.equal(numberFormat(0.5, 2, 'fr_FR'), '0,5');
});

test('a value that is no figure comes back as it came', () => {
  assert.equal(numberFormat('124/75', 2, 'en'), '124/75');
  assert.equal(numberFormat('', 2, 'en'), '');
  assert.equal(numberFormat(null, 2, 'en'), '');
});

test('the decimals of a step', () => {
  assert.equal(numberStepDigits(1), 0);
  assert.equal(numberStepDigits(0.05), 2);
  assert.equal(numberStepDigits('0.5'), 1);
  assert.equal(numberStepDigits(null), 0);
});
