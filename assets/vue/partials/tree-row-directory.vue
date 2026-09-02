<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-tree-row-directory',

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
    },
    isOpen: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    caretHtml() {
      return this.app.getServiceOrFail('icon').icon('ph:bold/caret-right');
    },

    iconHtml() {
      return this.app.getServiceOrFail('icon').icon(
        this.isOpen ? 'ph:bold/folder-open' : 'ph:bold/folder'
      );
    }
  }
};
</script>
