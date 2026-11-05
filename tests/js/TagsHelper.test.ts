// What both faces of the tags field read the same way: the vue and the server
// field's script (components/form/tags-input) call these alone.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';

register('./hooks.mjs', import.meta.url);

const {
  tagsFilterSuggestions,
  tagsJoin,
  tagsMergeSuggestions,
  tagsNormalizeSuggestions,
  tagsSplit,
  tagsSuggestionsUrl
} = await import('../../assets/js/Helper/TagsHelper.ts');

test('what is typed is split, trimmed, each word once', () => {
  assert.deepEqual(tagsSplit(' a, b,,a ,c '), ['a', 'b', 'c']);
  assert.deepEqual(tagsSplit('a; b', ';'), ['a', 'b']);
  assert.equal(tagsJoin(['a', 'b']), 'a, b');
});

test('a source answers words, pairs, or either wrapped', () => {
  const expected = [{ value: 'a', label: 'a' }, { value: 'b', label: 'Bee' }];

  assert.deepEqual(tagsNormalizeSuggestions(['a', { value: 'b', label: 'Bee' }, '', 'a']), expected);
  assert.deepEqual(tagsNormalizeSuggestions({ data: ['a', { value: 'b', label: 'Bee' }] }), expected);
  assert.deepEqual(tagsNormalizeSuggestions({ items: ['a', { value: 'b', label: 'Bee' }] }), expected);
  assert.deepEqual(tagsNormalizeSuggestions(null), []);
});

test('the words offered leave out the taken, hold what is typed, the starting ones first', () => {
  const all = tagsNormalizeSuggestions(['follow-up', 'up-sell', 'urgent', 'vip']);

  assert.deepEqual(tagsFilterSuggestions(all, ['urgent'], '').map((s) => s.value), ['follow-up', 'up-sell', 'vip']);
  assert.deepEqual(tagsFilterSuggestions(all, [], 'UP').map((s) => s.value), ['up-sell', 'follow-up']);
  assert.equal(tagsFilterSuggestions(all, [], '', 2).length, 2);
});

test('the field\'s words come before the source\'s, each once', () => {
  const merged = tagsMergeSuggestions(tagsNormalizeSuggestions(['a', 'b']), tagsNormalizeSuggestions(['b', 'c']));

  assert.deepEqual(merged.map((s) => s.value), ['a', 'b', 'c']);
});

test('the source is asked with what is typed', () => {
  assert.equal(tagsSuggestionsUrl('/stamps', ' fo '), '/stamps?q=fo');
  assert.equal(tagsSuggestionsUrl('/stamps?project=1', 'a b'), '/stamps?project=1&q=a%20b');
});
