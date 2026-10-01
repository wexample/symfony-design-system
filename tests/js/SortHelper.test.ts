// The order of the vue table. src/Helper/SortHelper.php is its twin, and
// tests/Unit/Helper/SortHelperTest.php holds the same cases.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { sortApply, sortAria, sortNext, type SortState } from '../../assets/js/Helper/SortHelper.ts';

const DEFAULT: SortState = { key: 'measured', direction: 'desc' };

test('another column cycles ascending, descending, default', () => {
  const first = sortNext(null, 'name', DEFAULT);
  const second = sortNext(first, 'name', DEFAULT);

  assert.deepEqual(first, { key: 'name', direction: 'asc' });
  assert.deepEqual(second, { key: 'name', direction: 'desc' });
  assert.deepEqual(sortNext(second, 'name', DEFAULT), DEFAULT);
});

test('the default column turns, then comes back', () => {
  const turned = sortNext(DEFAULT, 'measured', DEFAULT);

  assert.deepEqual(turned, { key: 'measured', direction: 'asc' });
  assert.deepEqual(sortNext(turned, 'measured', DEFAULT), DEFAULT);
});

test('without a default, the third press gives the rows their order back', () => {
  assert.equal(sortNext({ key: 'name', direction: 'desc' }, 'name'), null);
});

test('aria-sort', () => {
  assert.equal(sortAria(DEFAULT, 'measured'), 'descending');
  assert.equal(sortAria({ key: 'name', direction: 'asc' }, 'name'), 'ascending');
  assert.equal(sortAria(DEFAULT, 'name'), 'none');
  assert.equal(sortAria(null, 'name'), 'none');
});

test('empty values stay last both ways', () => {
  const rows = [
    { name: 'none', measured: null },
    { name: 'old', measured: '2026-01-01T10:00:00Z' },
    { name: 'blank', measured: '' },
    { name: 'new', measured: '2026-09-30T10:00:00Z' },
  ];
  const names = (state: SortState) => sortApply(rows, state).map((row) => row.name);

  assert.deepEqual(names({ key: 'measured', direction: 'asc' }), ['old', 'new', 'none', 'blank']);
  assert.deepEqual(names({ key: 'measured', direction: 'desc' }), ['new', 'old', 'none', 'blank']);
});

test('words follow the locale: Élodie sits with the E\'s', () => {
  const rows = [{ name: 'Zoé' }, { name: 'Élodie' }, { name: 'adrien' }, { name: 'Eric' }];

  assert.deepEqual(
    sortApply(rows, { key: 'name', direction: 'asc' }, { locale: 'fr_FR' }).map((row) => row.name),
    ['adrien', 'Élodie', 'Eric', 'Zoé']
  );
});

test('numbers, dates, and figures inside words', () => {
  const sorted = (values: unknown[]) => sortApply(values.map((v) => ({ v })), { key: 'v', direction: 'asc' }).map((row) => row.v);

  assert.deepEqual(sorted([10, 9, 100]), [9, 10, 100]);
  assert.deepEqual(sorted(['Room 10', 'Room 9']), ['Room 9', 'Room 10']);
  assert.deepEqual(sorted([new Date('2026-02-01'), new Date('2026-01-01')]), [new Date('2026-01-01'), new Date('2026-02-01')]);
});

test('a dotted key, and groups sorted among themselves', () => {
  const rows = [
    { group: 'First' },
    { person: { last: 'Martin' } },
    { person: { last: 'Durand' } },
    { group: 'Second' },
    { person: { last: 'Bernard' } },
    { person: { last: 'Adam' } },
  ];
  const sorted = sortApply(rows, { key: 'person.last', direction: 'asc' }, { isFixed: (row: any) => row.group !== undefined });

  assert.deepEqual(
    sorted.map((row: any) => row.group ?? row.person.last),
    ['First', 'Durand', 'Martin', 'Second', 'Adam', 'Bernard']
  );
});

test('equal rows keep their order, and the list given is left as it was', () => {
  const rows = [{ id: 1, v: 'a' }, { id: 2, v: 'a' }, { id: 3, v: 'b' }];
  const sorted = sortApply(rows, { key: 'v', direction: 'desc' });

  assert.deepEqual(sorted.map((row) => row.id), [3, 1, 2]);
  assert.deepEqual(rows.map((row) => row.id), [1, 2, 3]);
});
