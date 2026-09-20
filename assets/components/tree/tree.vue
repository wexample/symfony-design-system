<script>
import TreeNode from "../tree-node/tree-node.vue";

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-tree',

  components: {
    // Declared here and nowhere else: components are registered on the app, so a
    // node can nest another node without listing itself, which would send the
    // registration walker round in circles.
    TreeNode
  },

  props: {
    items: {
      type: Array,
      required: true,
      default: () => []
    },

    // Maps an item type to the component rendering its row. Names, not component
    // objects, so the map survives the JSON transport props take from Twig.
    rowComponents: {
      type: Object,
      default: () => ({})
    },

    // Called with a node and a page number when that node opens, and answers the
    // level below as { items, total } — total being what the level holds in all,
    // which is how the tree knows there is a rest to offer. Left out, the tree
    // only shows the children it was handed.
    loadChildren: {
      type: Function,
      default: null
    },

    // Whether shift and ctrl extend the selection. Left off, a click replaces
    // what was selected whatever is held down.
    allowSelectMultiple: {
      type: Boolean,
      default: false
    }
  },

  emits: ['select', 'action'],

  data() {
    return {
      // An object rather than the items themselves: what goes down the provide
      // stays the same reference, and every depth sees it change.
      selection: {
        items: [],
        // Where a range starts. The last click that was not a range, so that
        // shifting twice from the same place widens instead of walking.
        anchor: null
      }
    };
  },

  created() {
    // Which item each drawn row stands for, held outside data: it is read when a
    // range is asked for, and making it reactive would redraw the tree on every
    // node that mounts.
    this.rowItems = new Map();
  },

  provide() {
    // Passed down rather than drilled: every depth needs it and the recursion has
    // no business carrying it.
    return {
      treeRowComponents: this.rowComponents,
      treeLoadChildren: this.loadChildren,
      treeSelection: this.selection,
      treeRegisterRow: this.registerRow,
      treeUnregisterRow: this.unregisterRow
    };
  },

  methods: {
    registerRow(el, item) {
      this.rowItems.set(el, item);
    },

    unregisterRow(el) {
      this.rowItems.delete(el);
    },

    // Clicking a row selects it and says so. What that means is the caller's to
    // decide — the tree only holds which ones they are.
    onSelect({ item, range, toggle }) {
      if (this.allowSelectMultiple && range) {
        this.selection.items = this.itemsBetweenAnchorAnd(item);
      } else if (this.allowSelectMultiple && toggle) {
        this.selection.items = this.selection.items.includes(item)
          ? this.selection.items.filter(selected => selected !== item)
          : [...this.selection.items, item];
        this.selection.anchor = item;
      } else {
        this.selection.items = [item];
        this.selection.anchor = item;
      }

      this.$emit('select', this.selection.items);
    },

    itemsBetweenAnchorAnd(item) {
      const visible = this.visibleItems();
      const from = visible.indexOf(this.selection.anchor);
      const to = visible.indexOf(item);

      // No anchor, or one that has since been folded away: the range has nothing
      // to stretch from and the click stands on its own.
      if (-1 === from || -1 === to) {
        return [item];
      }

      return visible.slice(Math.min(from, to), Math.max(from, to) + 1);
    },

    // Display order is the only order a range can mean, and the DOM is where it
    // is written: a node knows neither its siblings nor the levels unfolded above
    // it. Rows carrying no item, such as the one offering the rest of a level,
    // fall out on their own.
    visibleItems() {
      return Array.from(this.$el.querySelectorAll('.tree--row'))
        .map(el => this.rowItems.get(el))
        .filter(item => undefined !== item);
    }
  }
};
</script>
