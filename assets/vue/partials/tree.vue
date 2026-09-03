<script>
import TreeNode from "./tree-node.vue";

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
    }
  },

  emits: ['select', 'action'],

  data() {
    return {
      // An object rather than the item itself: what goes down the provide stays
      // the same reference, and every depth sees it change.
      selection: {
        item: null
      }
    };
  },

  provide() {
    // Passed down rather than drilled: every depth needs it and the recursion has
    // no business carrying it.
    return {
      treeRowComponents: this.rowComponents,
      treeLoadChildren: this.loadChildren,
      treeSelection: this.selection
    };
  },

  methods: {
    // Clicking a row selects it and says so. What that means is the caller's to
    // decide — the tree only holds which one it is.
    onSelect(item) {
      this.selection.item = item;
      this.$emit('select', item);
    }
  }
};
</script>
