// What an editable grid stores from what is typed or pasted, and how a table
// out of a spreadsheet is read into its rows.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';

register('./hooks.mjs', import.meta.url);

const {
  gridPasteRows,
  gridTableGuess,
  gridTableRead,
  gridTextToValue,
  gridValueToText,
} = await import('../../assets/js/Helper/GridHelper.ts');

const title = { key: 'title', label: 'Title', editor: 'text', required: true };
const quantity = { key: 'quantity', label: 'Quantity', editor: 'number', scale: 100 };
const price = { key: 'price', label: 'Price', editor: 'money', currency: 'EUR', required: true };
const rate = { key: 'rate', label: 'VAT', editor: 'select', scale: 100, options: [{ value: 2000, label: '20 %' }, { value: 550, label: '5,5 %' }] };
const goods = { key: 'goods', label: 'Goods', editor: 'checkbox' };

test('a stored value as the figure a person types', () => {
  assert.equal(gridValueToText(150, quantity, 'fr'), '1,5');
  assert.equal(gridValueToText(123456, price, 'en'), '1234.56');
  assert.equal(gridValueToText(500, { ...price, currency: 'JPY' }, 'en'), '500');
  assert.equal(gridValueToText(null, price, 'en'), '');
  assert.equal(gridValueToText('Design', title, 'en'), 'Design');
});

test('what is typed, stored', () => {
  assert.deepEqual(gridTextToValue('1,5', quantity, 'fr'), { value: 150 });
  assert.deepEqual(gridTextToValue('1 234,50 €', price, 'fr'), { value: 123450 });
  assert.deepEqual(gridTextToValue('$1,234.50', price, 'en'), { value: 123450 });
  assert.deepEqual(gridTextToValue('', price, 'en'), { value: null });
  assert.equal(gridTextToValue('soon', price, 'en'), null);
  assert.deepEqual(gridTextToValue('  Design ', title, 'en'), { value: 'Design' });
});

test('a select takes an option by its value, its label, or its figure', () => {
  assert.deepEqual(gridTextToValue('2000', rate, 'en'), { value: 2000 });
  assert.deepEqual(gridTextToValue('5,5 %', rate, 'fr'), { value: 550 });
  assert.deepEqual(gridTextToValue('20', rate, 'en'), { value: 2000 });
  assert.equal(gridTextToValue('7', rate, 'en'), null);
});

test('a box ticked by a word', () => {
  assert.deepEqual(gridTextToValue('oui', goods), { value: true });
  assert.deepEqual(gridTextToValue('', goods), { value: false });
  assert.equal(gridTextToValue('maybe', goods), null);
});

test('cells out of a spreadsheet are a table, a word alone is not', () => {
  assert.deepEqual(gridPasteRows('Design\t2\r\nPrinting\t1\n'), [['Design', '2'], ['Printing', '1']]);
  assert.equal(gridPasteRows('Design'), null);
});

test('columns found by the header naming them', () => {
  const table = gridTableGuess([['Price', 'Title'], ['10', 'Design']], [title, quantity, price], 'en');

  assert.deepEqual(table.columns, ['price', 'title']);
  assert.equal(table.header, true);
});

test('columns taken in order, a header told by a figure that is none', () => {
  const columns = [title, price];

  assert.deepEqual(gridTableGuess([['Design', '10'], ['Printing', '5']], columns, 'en'), {
    rows: [['Design', '10'], ['Printing', '5']],
    columns: ['title', 'price'],
    header: false,
  });
  assert.equal(gridTableGuess([['What', 'How much'], ['Design', '10']], columns, 'en').header, true);
});

test('a table read into values, its bad cells named, its blank rows dropped', () => {
  const records = gridTableRead({
    rows: [['Title', 'Price', 'VAT'], ['Design', '500,00', '20 %'], ['', '', ''], ['', 'soon', '']],
    columns: ['title', 'price', 'rate'],
    header: true,
  }, [title, price, rate], 'fr');

  assert.deepEqual(records, [
    { row: 1, values: { title: 'Design', price: 50000, rate: 2000 }, invalid: [] },
    { row: 3, values: {}, invalid: ['price', 'title'] },
  ]);
});
