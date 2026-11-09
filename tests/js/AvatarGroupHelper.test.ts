// The faces of a group as a pile shows them. src/Helper/AvatarGroupHelper.php
// is its twin, and tests/Unit/Helper/AvatarGroupHelperTest.php holds the same cases.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { avatarGroupSplit, avatarInitials } from '../../assets/js/Helper/AvatarGroupHelper.ts';

test('the first faces and the count of the rest', () => {
  assert.deepEqual(avatarGroupSplit(['a', 'b', 'c'], 4), { shown: ['a', 'b', 'c'], more: 0 });
  assert.deepEqual(avatarGroupSplit(['a', 'b', 'c', 'd'], 4), { shown: ['a', 'b', 'c', 'd'], more: 0 });
  assert.deepEqual(avatarGroupSplit(['a', 'b', 'c', 'd', 'e', 'f'], 4), { shown: ['a', 'b', 'c'], more: 3 });
  assert.deepEqual(avatarGroupSplit(['a', 'b'], 1), { shown: [], more: 2 });
});

test('the initials of a face with no picture', () => {
  assert.equal(avatarInitials('Romain Weeger'), 'RW');
  assert.equal(avatarInitials('maya'), 'MA');
  assert.equal(avatarInitials('élodie Lambert'), 'ÉL');
  assert.equal(avatarInitials('  '), '');
});
