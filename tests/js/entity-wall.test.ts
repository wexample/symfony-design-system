// A wall read as it is scrolled: each page is added under those held, and
// reading the wall again reads all of them at once — read off the component
// with a repository standing in for the api.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';
import { vueInstance } from './vue-instance.ts';

register('./hooks.mjs', import.meta.url);

const { default: Mixin } = await import('../../assets/js/Vue/AbstractEntityCollectionVueMixin.ts');
const { default: Wall } = await import('../../assets/components/collection/wall/abstract-entity-wall/abstract-entity-wall.vue?script');

const PHOTOS = Array.from({ length: 5 }, (value, index) => ({ id: index + 1, name: `Photo ${index + 1}`, width: 1600, height: 1200 }));

function mount() {
  const asked: Array<Record<string, unknown>> = [];
  const repository = {
    async fetchListPaginated({ page, length }: { page: number; length: number }) {
      asked.push({ page, length });
      const items = PHOTOS.slice(page * length, page * length + length);

      return { items, pagination: { page, length, total: PHOTOS.length, hasMore: page * length + length < PHOTOS.length } };
    },
  };
  const { vm } = vueInstance(Wall, {}, [Mixin]);
  vm.pageLength = 2;
  vm.getEntityRepository = () => repository;
  vm.$nextTick = () => {};

  return { vm, asked };
}

test('a tile is as tall as the picture of its entity says', async () => {
  const { vm } = mount();
  await vm.readEntitiesCollection();

  assert.deepEqual(vm.tiles.map((tile: any) => [tile.key, tile.span, tile.caption]), [[1, 9, 'Photo 1'], [2, 9, 'Photo 2']]);
});

test('the next page is added under the ones held, until there is none left', async () => {
  const { vm, asked } = mount();
  await vm.readEntitiesCollection();
  await vm.loadNewerEntities();
  await vm.loadNewerEntities();

  assert.deepEqual(vm.entities.map((entity: any) => entity.id), [1, 2, 3, 4, 5]);
  assert.equal(vm.hasNewerEntities, false);
  await vm.loadNewerEntities();
  assert.deepEqual(asked.map((query) => query.page), [0, 1, 2]);
});

test('read again, the wall asks for every page it holds in one request', async () => {
  const { vm, asked } = mount();
  await vm.readEntitiesCollection();
  await vm.loadNewerEntities();
  await vm.readEntitiesCollection({ silent: true });

  assert.deepEqual(asked.at(-1), { page: 0, length: 4 });
  assert.deepEqual(vm.entities.map((entity: any) => entity.id), [1, 2, 3, 4]);
});
