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
    }
  }
};
</script>
