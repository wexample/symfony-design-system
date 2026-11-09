// The block list: what it asks of its caller — a move one place up or down,
// never past either end; a removal, once agreed — and the item it opens.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const BlockList = (await import('../../assets/components/block-list/block-list.vue?script')).default;

const items = [{ id: 1 }, { id: 2 }, { id: 3 }];

test('a move is asked with its direction and the place it goes to, never past an end', () => {
  const { vm, emitted } = vueInstance(BlockList, { items });

  vm.move(items[1], 1, 'up');
  assert.deepEqual(emitted.at(-1), ['move', items[1], 'up', 0]);
  vm.move(items[1], 1, 'down');
  assert.deepEqual(emitted.at(-1), ['move', items[1], 'down', 2]);

  const count = emitted.length;
  vm.move(items[0], 0, 'up');
  vm.move(items[2], 2, 'down');
  assert.equal(emitted.length, count);
});

test('a removal asked of no question is said at once', async () => {
  const { vm, emitted } = vueInstance(BlockList, { items });

  await vm.remove(items[2], { currentTarget: { dataset: {} } });
  assert.deepEqual(emitted.at(-1), ['remove', items[2]]);
});

test('the item opened is the one its key names, whatever the key\'s type', () => {
  const { vm } = vueInstance(BlockList, { items, openKey: '2' });

  assert.equal(vm.isOpen(items[1]), true);
  assert.equal(vm.isOpen(items[0]), false);
  assert.equal(vueInstance(BlockList, { items, itemKey: 'id' }).vm.isOpen(items[0]), false);
  assert.equal(vueInstance(BlockList, { items: [{ uid: 'a' }], itemKey: 'uid', openKey: 'a' }).vm.isOpen({ uid: 'a' }), true);
});
