import { test } from 'node:test';
import assert from 'node:assert/strict';
import { imageFitWithin } from '../../assets/js/Helper/ImageHelper.ts';

test('the longer side comes down to the most allowed, the ratio kept', () => {
  assert.deepEqual(imageFitWithin(6000, 4000, 2560), { width: 2560, height: 1707 });
  assert.deepEqual(imageFitWithin(3000, 6000, 2560), { width: 1280, height: 2560 });
});

test('an image that already fits is left as it is', () => {
  assert.deepEqual(imageFitWithin(1920, 1080, 2560), { width: 1920, height: 1080 });
  assert.deepEqual(imageFitWithin(2560, 100, 2560), { width: 2560, height: 100 });
});
