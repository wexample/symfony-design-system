<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-tree-row-file-tree-row-file',

  // The row is where a per-type action is raised, and the node relays it up.
  emits: ['action'],

  // The node hands every row the same set, and a file uses none of the ones that
  // describe an opening. Nothing to fall through onto a fragment root either.
  inheritAttrs: false,

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
