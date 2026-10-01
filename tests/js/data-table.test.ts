// The order of the vue data table, read off the component itself: its props,
// computed and methods run on a bare instance, the template left out — what
// the template draws of it is aria-sort and the arrow, both straight from
// getSortAria().
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { default: DataTable } = await import('../../assets/components/data-table/data-table.vue?script');

function mount(props: Record<string, unknown>) {
  const mounted = vueInstance(DataTable, props);
  mounted.vm.trans = (key: string, args: Record<string, unknown> = {}) => `${key.split('.').pop()} ${JSON.stringify(args)}`;

  return mounted;
}

const rows = () => [
  { id: 1, first: 'Zoé', last: 'Martin', measured: '2026-09-01' },
  { id: 2, first: 'Élodie', last: 'Durand', measured: null },
  { id: 3, first: 'Adam', last: 'Petit', measured: '2026-09-30' },
  { id: 4, first: 'Eric', last: 'Bernard', measured: '2026-01-15' },
  { id: 5, first: 'Basile', last: 'Leroy', measured: '' },
];

const NAME = { key: 'first', label: 'Name', sortable: true, sortKey: 'last' };
const MEASURED = { key: 'measured', label: 'Last measurement', sortable: true };
const ACTIONS = { key: 'actions', actions: ['show'] };
const DEFAULT = { key: 'measured', direction: 'desc' };

const ids = (list: Array<{ id: number }>) => list.map((row) => row.id);

test('local: the whole list is sorted before it is paged', () => {
  const { vm } = mount({ rows: rows(), columns: [NAME, MEASURED], sortRows: true, pageSize: 2, app: {} });

  vm.toggleSort(NAME);

  // Bernard and Durand were on the second and fourth places, on two pages.
  assert.deepEqual(ids(vm.visibleRows), [4, 2]);
  assert.deepEqual(ids(vm.shownRows), [4, 2, 5, 1, 3]);
});

test('local: the default order holds from the start, empty measurements last', () => {
  const { vm } = mount({ rows: rows(), columns: [NAME, MEASURED], sortRows: true, defaultSort: DEFAULT, app: {} });

  assert.deepEqual(ids(vm.shownRows), [3, 1, 4, 2, 5]);
});

test('server: a press says the new order and leaves the rows as they came', () => {
  const given = rows();
  const { vm, emitted } = mount({ rows: given, columns: [NAME, MEASURED], defaultSort: DEFAULT, app: {} });

  vm.toggleSort(NAME);

  assert.deepEqual(emitted, [['update:sort', { key: 'last', direction: 'asc' }]]);
  assert.deepEqual(ids(vm.shownRows), ids(given));
});

test('the cycle: ascending, descending, back to the default', () => {
  const { vm, emitted } = mount({ rows: rows(), columns: [NAME, MEASURED], defaultSort: DEFAULT, app: {} });

  vm.toggleSort(NAME);
  vm.toggleSort(NAME);
  vm.toggleSort(NAME);

  assert.deepEqual(emitted.map(([, state]) => state), [
    { key: 'last', direction: 'asc' },
    { key: 'last', direction: 'desc' },
    DEFAULT,
  ]);
});

test('a parent holding the order through v-model:sort is what the table follows', () => {
  const { vm } = mount({ rows: rows(), columns: [NAME, MEASURED], defaultSort: DEFAULT, sort: { key: 'last', direction: 'desc' }, app: {} });

  assert.equal(vm.getSortAria(NAME), 'descending');
  assert.equal(vm.getSortAria(MEASURED), 'none');
});

test('aria-sort follows the order, and a column that does not sort has none', () => {
  const { vm } = mount({ rows: rows(), columns: [NAME, MEASURED, ACTIONS], defaultSort: DEFAULT, app: {} });

  assert.equal(vm.getSortAria(MEASURED), 'descending');
  assert.equal(vm.getSortAria(NAME), 'none');
  assert.equal(vm.getSortAria(ACTIONS), null);
  assert.equal(vm.isColumnSortable(ACTIONS), false);

  vm.toggleSort(NAME);

  assert.equal(vm.getSortAria(NAME), 'ascending');
  assert.equal(vm.getSortAria(MEASURED), 'none');
});

test('a new order goes back to the first page and is said aloud', () => {
  const { vm } = mount({ rows: rows(), columns: [NAME, MEASURED], sortRows: true, pageSize: 2, app: {} });

  vm.page = 2;
  vm.toggleSort(NAME);

  assert.equal(vm.page, 0);
  assert.equal(vm.sortMessage, 'sorted_asc {"%column%":"Name"}');
});

test('the ticked rows stay ticked, keyed or not', () => {
  [(row: { id: number }) => row.id, null].forEach((rowKey) => {
    const given = rows();
    const { vm } = mount({ rows: given, columns: [NAME, MEASURED], sortRows: true, selectable: true, rowKey, app: {} });

    vm.toggleRow(given[0], true);
    vm.toggleSort(NAME);
    DataTable.watch.shownRows.call(vm);

    assert.equal(vm.isRowSelected(given[0]), true);
    assert.deepEqual(vm.shownRows.filter((row: any) => vm.isRowSelected(row)), [given[0]]);
  });
});

test('a column can sort on what it computes', () => {
  const byFirst = { ...NAME, sortValue: (row: { first: string }) => row.first };
  const { vm } = mount({ rows: rows(), columns: [byFirst], sortRows: true, sortLocale: 'fr', app: {} });

  vm.toggleSort(byFirst);

  assert.deepEqual(vm.shownRows.map((row: any) => row.first), ['Adam', 'Basile', 'Élodie', 'Eric', 'Zoé']);
});
