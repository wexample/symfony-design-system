<script>
import ButtonMenu from '../button-menu/button-menu.vue';

// What a zone may not be dragged below: a zone dragged to nothing leaves a
// seam nobody can find again.
const SIZE_MIN = 80;

// One press of an arrow key on a seam.
const KEYBOARD_STEP = 16;

// One node of a zone-tiling: a split, drawing its children side by side or
// one above the other with a seam between each two, or a zone, drawing what
// the page puts in it under its menu. Drawn by itself, as deep as the tree
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

    isVertical() {
      return this.node.split === 'v';
    },

    menuItems() {
      return this.zoneTiling.menuItems(this.node);
    }
  },

  methods: {
    cellStyle(child) {
      return { flex: `${child.size ?? 1} 1 0` };
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
