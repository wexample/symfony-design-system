// Moving by dragging, read off the explorer itself: what may be dropped where,
// and what the page is told — the template only wires the drag events to it.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { default: FileExplorer } = await import('../../assets/components/file-explorer/file-explorer.vue?script');

const trips = { name: 'Trips', path: 'f1/f2', type: 'directory' };
const sea = { name: 'sea.png', path: 'f1/m1', type: 'file' };

const dragEvent = () => {
  const event = { prevented: false, dataTransfer: { effectAllowed: '', dropEffect: '', setData() {} }, preventDefault() { event.prevented = true; } };

  return event;
};

function mount(movable = true) {
  // data() reads the remembered view before the methods are bound here.
  const stateValue = (key: string, fallback: unknown) => fallback;
  const mounted = vueInstance(FileExplorer, { loadChildren: async () => ({ items: [], total: 0 }), movable, app: {}, stateValue });
  mounted.vm.items = [trips, sea];
  mounted.vm.path = 'f1';

  return mounted;
}

test('a dragged item lands in another folder, never where it is nor inside itself', () => {
  const { vm } = mount();
  vm.onDragStart(trips, dragEvent());

  assert.equal(vm.canDropOn(null), true, 'the root');
  assert.equal(vm.canDropOn('f3'), true, 'another folder');
  assert.equal(vm.canDropOn('f1'), false, 'where it already is');
  assert.equal(vm.canDropOn('f1/f2'), false, 'itself');
  assert.equal(vm.canDropOn('f1/f2/f4'), false, 'a folder it holds');
});

test('dropped, the page is told what moved and where, and the drag is over', () => {
  const { vm, emitted } = mount();
  vm.onDragStart(sea, dragEvent());
  const over = dragEvent();
  vm.onDragOver('f1/f2', over);

  assert.equal(over.prevented, true);
  assert.equal(vm.isDropTarget('f1/f2'), true);

  vm.onDrop(trips, dragEvent());

  assert.deepEqual(emitted, [['move', { items: [sea], target: trips }]]);
  assert.equal(vm.isDropTarget('f1/f2'), false);
});

test('a selected item drags the whole selection', () => {
  const { vm } = mount();
  vm.selectedKeys = ['f1/f2', 'f1/m1'];
  vm.onDragStart(sea, dragEvent());

  assert.deepEqual(vm.dragItems, [trips, sea]);
});

test('without movable, nothing is dragged', () => {
  const { vm } = mount(false);
  vm.onDragStart(sea, dragEvent());

  assert.deepEqual(vm.dragItems, []);
  assert.equal(vm.canDropOn(null), false);
});

