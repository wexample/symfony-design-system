import { test } from 'node:test';
import assert from 'node:assert/strict';
import { wallSpan } from '../../assets/js/Helper/WallHelper.ts';

test('a picture spans as many twelfths of a column as its height takes', () => {
  assert.equal(wallSpan(1200, 1200), 12);
  assert.equal(wallSpan(1600, 1200), 9);
  assert.equal(wallSpan(1200, 1600), 16);
});

test('a sliver or a tower is held between half and twice its width', () => {
  assert.equal(wallSpan(4000, 500), 6);
  assert.equal(wallSpan(500, 4000), 24);
});

test('a picture whose size is not known stands square', () => {
  assert.equal(wallSpan(null, null), 12);
  assert.equal(wallSpan(800, 0), 12);
});
