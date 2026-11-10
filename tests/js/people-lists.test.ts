// The lists of a conversation between people: the open conversation told
// by its id, a row with no address picked rather than followed; a person
// taken off asked for, named.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const ConversationList = (await import('../../assets/components/conversation-list/conversation-list.vue?script')).default;
const PersonList = (await import('../../assets/components/person-list/person-list.vue?script')).default;

test('the open conversation is the one its id names, whatever its type', () => {
  const { vm } = vueInstance(ConversationList, { items: [{ id: 1 }, { id: 2 }], activeId: '2' });

  assert.equal(vm.isActive({ id: 2 }), true);
  assert.equal(vm.isActive({ id: 1 }), false);
  assert.equal(vueInstance(ConversationList, { items: [] }).vm.isActive({ id: 1 }), false);
});

test('a row with no address is picked, one with an address followed', () => {
  const { vm, emitted } = vueInstance(ConversationList, { items: [] });
  let prevented = 0;
  const event = { preventDefault: () => { prevented += 1; } };

  vm.onSelect({ id: 3 }, event);
  assert.deepEqual(emitted.at(-1), ['select', { id: 3 }]);
  assert.equal(prevented, 1);

  vm.onSelect({ id: 4, href: '/c/4' }, event);
  assert.equal(emitted.length, 1);
  assert.equal(prevented, 1);
});

test('a person taken off is said by name', () => {
  const { vm } = vueInstance(PersonList, { items: [{ id: 1, label: 'Ada' }], removable: true });

  assert.equal(vm.removeText({ label: 'Ada' }), 'remove {"%name%":"Ada"}');
});
