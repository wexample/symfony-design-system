<script>
import ZoneTilingNode from '../zone-tiling-node/zone-tiling-node.vue';

// Where a split lays its zones: side by side (`h`), or one above the other (`v`).
export const ZONE_TILING_SPLIT_H = 'h';
export const ZONE_TILING_SPLIT_V = 'v';

// What the menu of a zone holds before a page adds its own.
export const ZONE_TILING_ACTION_SPLIT_H = 'zone-tiling:split-h';
export const ZONE_TILING_ACTION_SPLIT_V = 'zone-tiling:split-v';
export const ZONE_TILING_ACTION_CLOSE = 'zone-tiling:close';

const newId = () => (crypto.randomUUID?.() ?? `${Date.now()}-${Math.random()}`).slice(0, 8);

const clone = (value) => JSON.parse(JSON.stringify(value));

// A screen made of zones the reader cuts and sizes: one zone at first, a
// split side by side or one above the other from the menu each zone shows
// under the pointer, every zone cut again as it likes, every seam dragged.
//
// The arrangement is a tree, handed in and out with `v-model`: a split is
// `{ id, split: 'h' | 'v', size, children }`, a zone `{ id, size, ... }` —
// whatever else a zone carries is the page's (what it shows, a subject), kept
// as it is. `size` is a weight among siblings, not pixels: the arrangement
// holds on any screen. What a zone shows is the `zone` slot's; the design
// system knows nothing of it.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-tiling-zone-tiling',

  components: {
    ZoneTilingNode
  },

  emits: ['update:modelValue', 'action'],

  provide() {
    return {
      zoneTiling: this
    };
  },

  props: {
    modelValue: {
      type: Object,
      default: null
    },
    // Each zone on its own ground, the page showing between them; flush, a
    // wall of screens parted by a line.
    flush: {
      type: Boolean,
      default: false
    },
    // `(zone, items) => items`: what the menu of a zone holds — the split
    // and close actions given, with the page's own. An item the page adds
    // runs its `run(zone, tiling)`, and is told (`action`) either way.
    zoneMenu: {
      type: Function,
      default: null
    }
  },

  data() {
    return {
      layout: this.normalize(this.modelValue)
    };
  },

  watch: {
    modelValue(value) {
      if (JSON.stringify(value) !== JSON.stringify(this.layout)) {
        this.layout = this.normalize(value);
      }
    }
  },

  methods: {
    // A tree handed in as it was saved, or nothing: one zone.
    normalize(value) {
      return value ? clone(value) : { id: newId(), size: 1 };
    },

    // The tree as it now is, told to whoever holds it.
    commit() {
      this.$emit('update:modelValue', clone(this.layout));
    },

    find(id, node = this.layout, parent = null) {
      if (node.id === id) {
        return { node, parent };
      }

      for (const child of node.children ?? []) {
        const found = this.find(id, child, node);

        if (found) {
          return found;
        }
      }

      return null;
    },

    isRoot(zone) {
      return zone.id === this.layout.id;
    },

    // The zone cut in two along `direction`, the new half empty. Cut along
    // the way its split already runs, it takes a place beside the zone rather
    // than nesting a split of one direction in another of the same.
    split(id, direction) {
      const found = this.find(id);

      if (!found) {
        return null;
      }

      const { node, parent } = found;
      const size = node.size ?? 1;
      const added = { id: newId(), size: size / 2 };

      if (parent && parent.split === direction) {
        node.size = size / 2;
        parent.children.splice(parent.children.indexOf(node) + 1, 0, added);
      } else {
        const kept = { ...clone(node), size: 1 };
        added.size = 1;

        Object.keys(node).forEach((key) => delete node[key]);
        Object.assign(node, { id: newId(), split: direction, size, children: [kept, added] });
      }

      this.commit();

      return added;
    },

    // The zone gone, its room to its neighbours; a split left with one zone
    // is that zone. The last one is emptied, never removed.
    close(id) {
      const found = this.find(id);

      if (!found) {
        return;
      }

      const { node, parent } = found;

      if (!parent) {
        this.layout = { id: newId(), size: 1 };
        this.commit();

        return;
      }

      parent.children.splice(parent.children.indexOf(node), 1);

      if (parent.children.length === 1) {
        const [only] = parent.children;
        const size = parent.size ?? 1;

        Object.keys(parent).forEach((key) => delete parent[key]);
        Object.assign(parent, only, { size });
      }

      this.commit();
    },

    // What a zone carries besides its place — what it shows, its settings —
    // changed: `patch` merged into it.
    update(id, patch) {
      const found = this.find(id);

      if (found) {
        Object.assign(found.node, patch);
        this.commit();
      }
    },

    menuItems(zone) {
      const items = [
        { value: ZONE_TILING_ACTION_SPLIT_H, icon: 'ph:bold/columns', label: this.trans('@vue::zone.split_h') },
        { value: ZONE_TILING_ACTION_SPLIT_V, icon: 'ph:bold/rows', label: this.trans('@vue::zone.split_v') }
      ];

      if (!this.isRoot(zone)) {
        items.push(
          { separator: true },
          { value: ZONE_TILING_ACTION_CLOSE, icon: 'ph:bold/x', label: this.trans('@vue::zone.close') }
        );
      }

      return this.zoneMenu ? this.zoneMenu(zone, items, this) : items;
    },

    onMenuSelect(zone, item) {
      if (item.value === ZONE_TILING_ACTION_SPLIT_H) {
        this.split(zone.id, ZONE_TILING_SPLIT_H);
      } else if (item.value === ZONE_TILING_ACTION_SPLIT_V) {
        this.split(zone.id, ZONE_TILING_SPLIT_V);
      } else if (item.value === ZONE_TILING_ACTION_CLOSE) {
        this.close(zone.id);
      } else {
        item.run?.(zone, this);
      }

      this.$emit('action', { zone, item });
    }
  }
};
</script>
