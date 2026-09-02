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

    // Called with a node the first time it opens, and answers the level below.
    // Left out, the tree only shows the children it was handed.
    loadChildren: {
      type: Function,
      default: null
    }
  },

  provide() {
    // Passed down rather than drilled: every depth needs it and the recursion has
    // no business carrying it.
    return {
      treeRowComponents: this.rowComponents,
      treeLoadChildren: this.loadChildren
    };
  }
};
</script>
