<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-tree-node',

  props: {
    item: {
      type: Object,
      required: true
    },
    depth: {
      type: Number,
      default: 0
    }
  },

  data() {
    return {
      // The item says how the node starts, the node owns it afterwards — that is
      // the whole difference with the Twig version, which cannot toggle.
      isOpen: this.item.open === true
    };
  },

  computed: {
    hasChildren() {
      return Array.isArray(this.item.children) && this.item.children.length > 0;
    },

    caretHtml() {
      return this.hasChildren ? this.renderIcon('ph:bold/caret-right') : '';
    },

    iconHtml() {
      return this.item.icon ? this.renderIcon(this.item.icon) : '';
    }
  },

  methods: {
    renderIcon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    },

    onRowClick() {
      if (this.hasChildren) {
        this.isOpen = !this.isOpen;
      }

      this.$emit('select', this.item);
    }
  }
};
</script>
