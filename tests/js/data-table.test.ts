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
  assert.equal(vm.statusMessage, 'sorted_asc {"%column%":"Name"}');
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

// The states that stand in for rows, and a row changed in place.

test('empty because of the filters is told apart from empty for real', () => {
  const filters = [{ key: 'owner', label: 'Owner', options: [] }];

  assert.equal(mount({ rows: [], columns: [NAME], filters, filterValues: { owner: ['a'] }, app: {} }).vm.isFilteredEmpty(), true);
  assert.equal(mount({ rows: [], columns: [NAME], filters, filterValues: {}, app: {} }).vm.isFilteredEmpty(), false);
  assert.equal(mount({ rows: rows(), columns: [NAME], filters, filterValues: { owner: ['a'] }, app: {} }).vm.isFilteredEmpty(), false);
});

test('clearing the filters empties them and goes back to the first page', () => {
  const { vm, emitted } = mount({ rows: [], columns: [NAME], filters: [{ key: 'owner', label: 'Owner', options: [] }], app: {} });

  vm.page = 2;
  vm.clearFilters();

  assert.deepEqual(emitted, [['update:filterValues', {}]]);
  assert.equal(vm.page, 0);
});

test('a row updated in place keeps the order, the page and the selection', () => {
  const given = rows();
  const { vm } = mount({ rows: given, columns: [NAME, MEASURED], sortRows: true, pageSize: 2, selectable: true, rowKey: (row: { id: number }) => row.id, app: {} });

  vm.toggleSort(NAME);
  vm.toggleSort(NAME);
  vm.page = 1;
  vm.toggleRow(given[3], true);
  // What the api sends back after deactivating Leroy: the same rows, his changed.
  vm.rows = given.map((row) => (row.id === 5 ? { ...row, first: 'Basile (deactivated)' } : { ...row }));
  DataTable.watch.shownRows.call(vm);

  assert.deepEqual(vm.resolvedSort, { key: 'last', direction: 'desc' });
  assert.equal(vm.page, 1);
  assert.deepEqual(vm.selectedKeys, [4]);
  assert.deepEqual(vm.visibleRows.map((row: { first: string }) => row.first), ['Basile (deactivated)', 'Élodie']);
});

test('a default order on a column the reader is not shown still orders, and no header claims it', () => {
  const { vm } = mount({ rows: rows(), columns: [NAME], sortRows: true, defaultSort: { key: 'measured', direction: 'desc' }, app: {} });

  assert.deepEqual(vm.shownRows.map((row: { id: number }) => row.id), [3, 1, 4, 2, 5]);
  assert.equal(vm.getSortAria(NAME), 'none');
});

// Searching a list the table holds whole.

const CITY = { key: 'city', label: 'City' };
const people = () => [
  { id: 1, first: 'Élodie', last: 'Durand', city: 'Lyon', measured: '2026-09-01' },
  { id: 2, first: 'Eric', last: 'Martin', city: 'Évry', measured: '2026-09-02' },
  { id: 3, first: 'Zoé', last: 'Petit', city: 'Lyon', measured: '2026-09-03' },
  { id: 4, first: 'Adam', last: 'Leroy', city: 'Angers', measured: '2026-09-04' },
];
const type = (vm: any, text: string) => vm.onSearchInput({ target: { value: text } });

test('the search finds what is typed, case and accents aside, on the searched columns only', () => {
  const { vm } = mount({ rows: people(), columns: [NAME, CITY, { ...MEASURED, cell: 'date' }], searchable: true, app: {} });

  type(vm, 'elodie');
  assert.deepEqual(ids(vm.shownRows), [1]);

  type(vm, 'EVRY');
  assert.deepEqual(ids(vm.shownRows), [2]);

  // The date column is not searched.
  type(vm, '2026-09');
  assert.deepEqual(ids(vm.shownRows), []);
});

test('a column can opt out of the search', () => {
  const { vm } = mount({ rows: people(), columns: [NAME, { ...CITY, searchable: false }], searchable: true, app: {} });

  type(vm, 'lyon');

  assert.deepEqual(ids(vm.shownRows), []);
});

test('the search narrows with the filters, then the order and the pages cut it', () => {
  const { vm } = mount({
    rows: people(),
    columns: [NAME, CITY],
    searchable: true,
    filters: [{ key: 'city', label: 'City', options: [] }],
    filterRows: true,
    filterValues: { city: ['Lyon'] },
    sortRows: true,
    pageSize: 1,
    app: {},
  });

  vm.page = 1;
  type(vm, 'o');
  vm.toggleSort({ ...NAME });

  assert.equal(vm.page, 0);
  assert.deepEqual(ids(vm.shownRows), [1, 3]);
  assert.deepEqual(ids(vm.visibleRows), [1]);
});

test('a search goes back to the first page and says how much it found', () => {
  const { vm } = mount({ rows: people(), columns: [NAME, CITY], searchable: true, pageSize: 1, app: {} });

  vm.page = 2;
  type(vm, 'lyon');

  assert.equal(vm.page, 0);
  assert.equal(vm.statusMessage, 'results {"%count%":2}');
});

test('a search finding nothing is the filtered-empty state, and clearing empties the search too', () => {
  const { vm } = mount({ rows: people(), columns: [NAME, CITY], searchable: true, app: {} });

  type(vm, 'nobody');
  assert.equal(vm.isFilteredEmpty(), true);

  vm.clearFilters();
  assert.equal(vm.search, '');
  assert.deepEqual(ids(vm.shownRows), [1, 2, 3, 4]);
});

test('a group heading with none of its rows found goes too', () => {
  const { vm } = mount({
    rows: [{ group: 'North' }, ...people().slice(0, 2), { group: 'South' }, ...people().slice(2)],
    columns: [NAME, CITY],
    searchable: true,
    app: {},
  });

  type(vm, 'angers');

  assert.deepEqual(vm.shownRows.map((row: any) => row.group ?? row.id), ['South', 4]);
});

test('without the option, a table holds no search', () => {
  const { vm } = mount({ rows: people(), columns: [NAME, CITY], app: {} });

  vm.search = 'lyon';

  assert.deepEqual(ids(vm.shownRows), [1, 2, 3, 4]);
});

test('fed by an api, the search narrows nothing and says what was typed', () => {
  const { vm, emitted } = mount({ rows: people(), columns: [NAME, CITY], searchable: true, searchRows: false, app: {} });

  type(vm, 'lyon');

  assert.deepEqual(ids(vm.shownRows), [1, 2, 3, 4]);
  assert.deepEqual(emitted.filter(([event]) => event === 'update:search'), [['update:search', 'lyon']]);
  assert.equal(vm.statusMessage, '');

  vm.clearFilters();

  assert.deepEqual(emitted.filter(([event]) => event === 'update:search').pop(), ['update:search', '']);
});

test('an actions cell with no actions declared reads them off the row, as the server table does', () => {
  const { vm } = mount({ rows: [], columns: [], app: {} });
  const column = { key: 'operations', cell: 'actions' };
  const row = { operations: [
    { icon: 'ph:bold/trash', href: '/delete', method: 'POST', token: 't', label: 'Delete' },
    { icon: 'ph:bold/pencil-simple', href: '/edit', target: 'panel', target_options: { closeOnEscape: true } },
    { href: '/nothing-to-draw' },
  ] };

  assert.equal(vm.hasCellActions(column), true);
  assert.deepEqual(vm.getCellActions(row, column), [
    { href: '/delete', target: '', targetOptions: {}, method: 'post', token: 't', label: 'Delete', icon: 'ph:bold/trash' },
    { href: '/edit', target: 'panel', targetOptions: { closeOnEscape: true }, method: 'get', token: undefined, label: undefined, icon: 'ph:bold/pencil-simple' },
  ]);
});

test('a row whose only action is a link is pressed as that link; two actions, or a post, are not', () => {
  const column = { key: 'operations', cell: 'actions' };
  const { vm } = mount({ rows: [], columns: [{ key: 'name' }, column], app: {} });
  const link = { icon: 'ph:bold/arrow-right', href: '/open' };

  assert.equal(vm.isRowLink({ operations: [link] }), true);
  assert.equal(vm.isRowLink({ operations: [link, { icon: 'ph:bold/trash', href: '/delete', method: 'post' }] }), false);
  assert.equal(vm.isRowLink({ operations: [{ icon: 'ph:bold/trash', href: '/delete', method: 'post' }] }), false);
  assert.equal(vm.isRowLink({ operations: [] }), false);
  assert.equal(vm.isRowLink({ group: 'North' }), false);
});
