// The text diff. src/Helper/DiffTextHelper.php is its twin, and
// tests/Unit/Helper/DiffTextHelperTest.php holds the same cases.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';

register('./hooks.mjs', import.meta.url);

const { diffTextLines, diffTextRows, diffTextSideBySide, diffTextStats } = await import('../../assets/js/Helper/DiffTextHelper.ts');

const shape = (lines) => lines.map((line) => (line.type === 'fold' ? `~${line.count}` : `${line.type[0]}${line.old ?? '-'}:${line.new ?? '-'} ${line.text}`));

test('lines kept, removed and added, each with its numbers', () => {
  assert.deepEqual(shape(diffTextLines('a\nb\nc', 'a\nB\nc\nd')), ['s1:1 a', 'r2:- b', 'a-:2 B', 's3:3 c', 'a-:4 d']);
});

test('nothing before is every line added; nothing after every line removed', () => {
  assert.deepEqual(shape(diffTextLines('', 'x\ny')), ['a-:1 x', 'a-:2 y']);
  assert.deepEqual(shape(diffTextLines('x', '')), ['r1:- x']);
  assert.deepEqual(diffTextLines('same', 'same').map((line) => line.type), ['same']);
});

test('a line changed is given its words, the ones changed marked', () => {
  const [removed, added] = diffTextLines('Hello {{ name }}!', 'Hello {{ user.name }}!');

  // Nothing of the old line went: the new one only gained « user. ».
  assert.deepEqual(removed.parts, [{ text: 'Hello {{ name }}!', changed: false }]);
  assert.deepEqual(added.parts, [{ text: 'Hello {{ ', changed: false }, { text: 'user.', changed: true }, { text: 'name }}!', changed: false }]);

  const [was, is] = diffTextLines('Status: draft', 'Status: published');

  assert.deepEqual(was.parts, [{ text: 'Status: ', changed: false }, { text: 'draft', changed: true }]);
  assert.deepEqual(is.parts, [{ text: 'Status: ', changed: false }, { text: 'published', changed: true }]);
});

test('the lines kept far from any change are folded, how many said', () => {
  const before = Array.from({ length: 12 }, (_, i) => `line ${i + 1}`).join('\n');
  const after = before.replace('line 6', 'line six');

  assert.deepEqual(shape(diffTextRows(before, after, 1)), ['~4', 's5:5 line 5', 'r6:- line 6', 'a-:6 line six', 's7:7 line 7', '~5']);
  assert.deepEqual(diffTextStats(diffTextRows(before, after, 1)), { added: 1, removed: 1 });
});

test('side by side, a removal faces the addition that replaced it', () => {
  const pairs = diffTextSideBySide(diffTextRows('a\nb\nc', 'a\nB\nX\nc', -1));

  assert.deepEqual(pairs.map((pair) => `${pair.left?.text ?? '_'}|${pair.right?.text ?? '_'}`), ['a|a', 'b|B', '_|X', 'c|c']);
});
