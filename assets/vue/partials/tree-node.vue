<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-tree-node',

  inject: {
    treeRowComponents: {
      default: () => ({})
    },
    // Given a node, answers the level below it. Absent, a tree only shows the
    // children it was handed.
    treeLoadChildren: {
      default: null
    }
  },

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
      isOpen: this.item.open === true,
      // Null means "never asked": what tells a level yet to load from one that
      // loaded and came back empty.
      children: this.item.children ?? null,
      isLoading: false
    };
  },

  computed: {
    hasChildren() {
      return Array.isArray(this.children) && this.children.length > 0;
    },

    caretHtml() {
      return this.hasChildren ? this.renderIcon('ph:bold/caret-right') : '';
    },

    iconHtml() {
      return this.item.icon ? this.renderIcon(this.item.icon) : '';
    },

    rowComponent() {
      return this.treeRowComponents[this.item.type] || null;
    },

    // An item written by hand says label, an entity says name: resolved here so
    // no caller has to remap a collection on its way in.
    label() {
      return this.item.label ?? this.item.name ?? '';
    }
  },

  methods: {
    renderIcon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    },

    async onRowClick() {
      this.isOpen = !this.isOpen;

      if (this.isOpen && this.children === null && this.treeLoadChildren) {
        this.isLoading = true;

        try {
          this.children = await this.treeLoadChildren(this.item);
        } finally {
          this.isLoading = false;
        }
      }

      this.$emit('select', this.item);
    }
  }
};
</script>
