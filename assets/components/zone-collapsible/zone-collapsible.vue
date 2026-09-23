<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-collapsible-zone-collapsible',

  props: {
    // Names the region so it can be remembered. Without one it still folds, it
    // just forgets — a page that did not name it did not ask for memory.
    id: {
      type: String,
      default: null
    },
    title: {
      type: String,
      default: ''
    },
    // The state it opens in. The parent may keep it in sync through
    // `update:collapsed`, or leave it alone and let the region hold its own.
    collapsed: {
      type: Boolean,
      default: false
    },
    padded: {
      type: Boolean,
      default: true
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  emits: ['update:collapsed'],

  data() {
    return {
      isCollapsed: this.collapsed
    };
  },

  watch: {
    collapsed(value) {
      this.isCollapsed = value;
    }
  },

  computed: {
    caret() {
      return this.app.getServiceOrFail('icon').icon('ph:bold/caret-left');
    },

    toggleLabel() {
      return this.trans(this.isCollapsed ? '@vue::zone.expand' : '@vue::zone.collapse');
    }
  },

  methods: {
    onToggle() {
      this.isCollapsed = !this.isCollapsed;
      this.$emit('update:collapsed', this.isCollapsed);

      if (this.id) {
        this.app.persistUiState(`ui.layout.zone.${this.id}`, !this.isCollapsed);
      }
    }
  }
};
</script>
