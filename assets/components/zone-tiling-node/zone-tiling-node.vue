<script>
import ButtonMenu from '../button-menu/button-menu.vue';
// What a zone may not be dragged below: a zone dragged to nothing leaves a
// seam nobody can find again.
const SIZE_MIN = 80;

// One press of an arrow key on a seam.
const KEYBOARD_STEP = 16;

// The room between two zones of a split, as the split draws it.
const GAP = 'var(--zone-gap, var(--space-2))';

// The axis a split lays its children along: side by side, their widths;
// one above the other, their heights.
const AXIS_OF_SPLIT = { h: 'width', v: 'height' };

// What a node holds to along `axis` ('width' | 'height'), as a CSS length, or
// null when it shares the room: a zone, the size it was locked at (`lock`);
// a split along that axis, its children laid end to end — all of them held,
// or none —; a split across it, the largest of those held.
export function zoneTilingFixedSize(node, axis) {
  if (!Array.isArray(node.children)) {
    const size = node.lock?.[axis];

    return size ? `${size}px` : null;
  }

  const sizes = node.children.map((child) => zoneTilingFixedSize(child, axis));
  const held = sizes.filter(Boolean);

  if (AXIS_OF_SPLIT[node.split] === axis) {
    return held.length === sizes.length
      ? `calc(${held.join(' + ')} + ${sizes.length - 1} * ${GAP})`
      : null;
  }

  return held.length ? `max(${held.join(', ')})` : null;
}

// One node of a zone-tiling: a split, drawing its children side by side or
// one above the other with a seam between each two, or a zone, drawing what
// the page puts in it — or, while the screen is arranged, what can be done
// to it. Drawn by itself, as deep as the tree
// goes; what it changes is the tiling's (`zoneTiling`), which holds the tree.
export default {
  name: 'ZoneTilingNode',

  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-tiling-node-zone-tiling-node',

  components: {
    ButtonMenu
  },

  inject: ['zoneTiling'],

  props: {
    node: {
      type: Object,
      required: true
    }
  },

  computed: {
    isSplit() {
      return Array.isArray(this.node.children);
    },

    hasBar() {
      return Boolean(this.node.toolbar);
    },

    isVertical() {
      return this.node.split === 'v';
    },

    menuItems() {
      return this.zoneTiling.menuItems(this.node);
    },

    axis() {
      return AXIS_OF_SPLIT[this.node.split] ?? null;
    },

    // What a zone is held to, each way: its width, its height.
    lockedWidth() {
      return Boolean(this.node.lock?.width);
    },

    lockedHeight() {
      return Boolean(this.node.lock?.height);
    },

    // What the zone is, said while the screen is arranged: the page's words.
    label() {
      return this.zoneTiling.zoneLabel ? this.zoneTiling.zoneLabel(this.node) : null;
    }
  },

  methods: {
    // A child holding to a size along the split takes that size and no
    // other, whatever room is left: past the screen, the tiling scrolls.
    // Held across it, it sets how wide — or tall — the split stands.
    cellStyle(child) {
      const held = zoneTilingFixedSize(child, this.axis);
      const across = zoneTilingFixedSize(child, this.axis === 'width' ? 'height' : 'width');
      const style = held ? { flex: `0 0 ${held}` } : { flex: `${child.size ?? 1} 1 0` };

      if (across) {
        style[this.axis === 'width' ? 'minHeight' : 'minWidth'] = across;
      }

      return style;
    },

    // A seam beside a child holding its size along the split moves nothing:
    // there is none.
    hasSeam(index) {
      return index < this.node.children.length - 1
        && !zoneTilingFixedSize(this.node.children[index], this.axis)
        && !zoneTilingFixedSize(this.node.children[index + 1], this.axis);
    },

    // The zone held to the size it now has on `axis`, or let go.
    toggleLock(axis) {
      const size = axis === 'width' ? this.$el.offsetWidth : this.$el.offsetHeight;
      const lock = { ...(this.node.lock ?? {}), [axis]: this.node.lock?.[axis] ? null : Math.round(size) };

      this.node.lock = lock.width || lock.height ? lock : undefined;
      this.zoneTiling.commit();
    },

    // The two zones a seam parts, as wide — or tall — as they now are.
    pairSizes(index) {
      const cells = this.$refs.split?.children;
      const measure = (el) => (this.isVertical ? el.offsetHeight : el.offsetWidth);

      return [measure(cells[index]), measure(cells[index + 1])];
    },

    // The first of the two takes `size` pixels of what the pair holds, the
    // second the rest; written as their weights, so the arrangement keeps on
    // another screen.
    resizePair(index, size, sizes) {
      const [first, second] = [this.node.children[index], this.node.children[index + 1]];
      const total = sizes[0] + sizes[1];
      const weight = (first.size ?? 1) + (second.size ?? 1);
      const clamped = Math.min(Math.max(size, SIZE_MIN), total - SIZE_MIN);

      first.size = (weight * clamped) / total;
      second.size = weight - first.size;
    },

    // In a page read from the right, a seam dragged right shrinks the zone after it.
    pointerDelta(event, origin) {
      const delta = this.isVertical ? event.clientY - origin : event.clientX - origin;

      return !this.isVertical && getComputedStyle(this.$el).direction === 'rtl' ? -delta : delta;
    },

    onSeamDown(index, event) {
      if (event.button !== 0) {
        return;
      }

      event.preventDefault();

      const handle = event.currentTarget;
      const origin = this.isVertical ? event.clientY : event.clientX;
      const sizes = this.pairSizes(index);

      const move = (moveEvent) => this.resizePair(index, sizes[0] + this.pointerDelta(moveEvent, origin), sizes);
      const end = () => {
        handle.removeEventListener('pointermove', move);
        handle.removeEventListener('pointerup', end);
        handle.removeEventListener('pointercancel', end);
        this.zoneTiling.commit();
      };

      handle.setPointerCapture(event.pointerId);
      handle.addEventListener('pointermove', move);
      handle.addEventListener('pointerup', end);
      handle.addEventListener('pointercancel', end);
    },

    onSeamKey(index, event) {
      const keys = this.isVertical ? { ArrowUp: -1, ArrowDown: 1 } : { ArrowLeft: -1, ArrowRight: 1 };
      const way = keys[event.key];

      if (!way) {
        return;
      }

      event.preventDefault();

      const sizes = this.pairSizes(index);

      this.resizePair(index, sizes[0] + way * KEYBOARD_STEP, sizes);
      this.zoneTiling.commit();
    },

    // Back to an even share between the two, as a resized zone is on a
    // double click.
    onSeamReset(index) {
      const [first, second] = [this.node.children[index], this.node.children[index + 1]];
      const half = ((first.size ?? 1) + (second.size ?? 1)) / 2;

      first.size = half;
      second.size = half;
      this.zoneTiling.commit();
    }
  }
};
</script>
