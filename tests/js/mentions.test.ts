// Someone named in a message: the mention typed found before the caret, the
// people it matches, the person put in its place, those a message names, and
// the names set apart in what is drawn — outside tags only.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { mentionQuery, mentionMatches, mentionInsert, mentionFound, mentionMark } = await import('../../assets/js/Helper/MentionHelper.ts');
const Composer = (await import('../../assets/components/composer/composer.vue?script')).default;

const people = [
  { id: 1, label: 'Maya Chen', handle: 'maya' },
  { id: 2, label: 'Élodie Martin' },
  { id: 3, label: 'Tom Ruiz', handle: 'tom' }
];

test('the mention typed is found before the caret, and only there', () => {
  assert.deepEqual(mentionQuery('Hello @ma', 9), { start: 6, query: 'ma' });
  assert.deepEqual(mentionQuery('@', 1), { start: 0, query: '' });
  assert.equal(mentionQuery('mail@host', 9), null);
  assert.equal(mentionQuery('Hello @maya and', 15), null);
});

test('the people a query names: starting with it first, accents and case aside', () => {
  assert.deepEqual(mentionMatches(people, 'el').map((p) => p.id), [2]);
  assert.deepEqual(mentionMatches(people, 'ELO').map((p) => p.id), [2]);
  assert.deepEqual(mentionMatches(people, 'm').map((p) => p.id), [1, 2, 3]);
  assert.deepEqual(mentionMatches(people, '', 2).map((p) => p.id), [1, 2]);
});

test('a person put in place of the mention typed, the caret after it', () => {
  assert.deepEqual(mentionInsert('Hi @ma there', 3, 6, people[0]), { text: 'Hi @maya there', caret: 9 });
  assert.deepEqual(mentionInsert('@', 0, 1, people[1]), { text: '@ÉlodieMartin ', caret: 14 });
});

test('the people a message names, and their names set apart outside tags', () => {
  assert.deepEqual(mentionFound('Ask @maya and @tom, not @nobody.', people).map((p) => p.id), [1, 3]);
  assert.equal(
    mentionMark('<p>Ask @maya.</p><a href="/@maya">@tom</a>', people),
    '<p>Ask <span class="mention" title="Maya Chen">@maya</span>.</p><a href="/@maya"><span class="mention" title="Tom Ruiz">@tom</span></a>'
  );
});

test('the composer chooses among the people offered with its keys, Enter sending otherwise', () => {
  const sent: unknown[][] = [];
  const { vm } = vueInstance(Composer, { mentions: people, submit: async (...args: unknown[]) => { sent.push(args); } });
  const input = { selectionStart: 0, focus() {}, setSelectionRange(start: number) { this.selectionStart = start; } };
  vm.$refs = { input };
  vm.$nextTick = (fn: () => void) => fn();
  const key = (name: string) => { let prevented = false; vm.onKeydown({ key: name, shiftKey: false, ctrlKey: false, altKey: false, metaKey: false, preventDefault: () => { prevented = true; } }); return prevented; };

  vm.draft = 'Ask @';
  input.selectionStart = 5;
  vm.readMention();
  assert.deepEqual(vm.mentionOffered.map((p: any) => p.id), [1, 2, 3]);

  key('ArrowDown');
  key('ArrowDown');
  assert.equal(vm.mentionIndex, 2);
  key('Enter');
  assert.equal(vm.draft, 'Ask @tom ');
  assert.equal(vm.mention, null);
  assert.equal(sent.length, 0);
});
