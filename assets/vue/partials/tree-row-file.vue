<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-tree-row-file',

  // The row is where a per-type action is raised, and the node relays it up.
  emits: ['action'],

  props: {
    item: {
      type: Object,
      required: true
    },
    label: {
      type: String,
      default: ''
    }
  },

  computed: {
    iconHtml() {
      return this.app.getServiceOrFail('icon').icon(this.item.icon || 'ph:bold/file');
    }
  }
};
</script>
