// A conversation between people, as the thread reads it: a line going on
// from the one before — its author, soon after —, the days it spans, the
// first line unread; and a face with whether it is there.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { threadContinued, threadDayKey, threadDayLabel } = await import('../../assets/js/Helper/ThreadHelper.ts');
const Avatar = (await import('../../assets/components/avatar/avatar.vue?script')).default;
const Thread = (await import('../../assets/components/collection/thread/abstract-entity-thread/abstract-entity-thread.vue?script')).default;

const at = (hours: number, minutes = 0) => new Date(2026, 10, 9, hours, minutes).toISOString();

test('a line goes on from the one before: its author, its side, within five minutes', () => {
  const first = { author: 'Ada', own: false, date: at(10, 0) };

  assert.equal(threadContinued(first, { author: 'Ada', own: false, date: at(10, 4) }), true);
  assert.equal(threadContinued(first, { author: 'Ada', own: false, date: at(10, 6) }), false);
  assert.equal(threadContinued(first, { author: 'Bob', own: false, date: at(10, 1) }), false);
  assert.equal(threadContinued(first, { author: 'Ada', own: true, date: at(10, 1) }), false);
  assert.equal(threadContinued(null, first), false);
  assert.equal(threadContinued(first, { author: 'Ada', own: false, date: null }), false);
});

test('a day is said today, yesterday, else as a date', () => {
  const now = new Date(2026, 10, 9, 12);
  const words = { today: 'Today', yesterday: 'Yesterday' };

  assert.equal(threadDayKey(new Date(2026, 10, 9, 23, 59)), '2026-11-09');
  assert.equal(threadDayLabel('2026-11-09', words, 'en', now), 'Today');
  assert.equal(threadDayLabel('2026-11-08', words, 'en', now), 'Yesterday');
  assert.equal(threadDayLabel('2026-11-02', words, 'en', now), 'Monday, November 2');
  assert.match(threadDayLabel('2025-11-02', words, 'en', now), /2025/);
});

test('the thread puts its dividers between the lines and groups an author\'s', () => {
  const entities = [
    { id: 1, author: 'Ada', createdAt: new Date(2026, 10, 8, 9, 0).toISOString() },
    { id: 2, author: 'Ada', createdAt: new Date(2026, 10, 8, 9, 2).toISOString() },
    { id: 3, author: 'Bob', createdAt: at(10, 0) },
    { id: 4, author: 'Bob', createdAt: at(10, 1) }
  ];
  const { vm } = vueInstance(Thread, { dayDividers: true, unreadSince: at(9, 30) });
  vm.entities = entities;
  vm.isOwnMessage = () => false;
  vm.dayLabel = (day: string) => day;
  vm.trans = () => 'New messages';

  const items = vm.threadItems;

  assert.deepEqual(items.map((item: any) => item.divider ?? `${item.key}${item.continued ? '+' : ''}`), [
    'day', '1', '2+', 'day', 'unread', '3', '4+'
  ]);
});

test('a face says whether it is there, in words for whoever reads it aloud', () => {
  const { vm } = vueInstance(Avatar, { label: 'Ada Lovelace', presence: 'online' });

  assert.equal(vm.initials, 'AL');
  assert.equal(vm.presenceWords, 'online');
});
