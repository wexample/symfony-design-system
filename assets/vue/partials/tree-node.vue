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
    },
    // Held by the tree, read by every depth: one item is selected across the
    // whole thing, so no node can own that state.
    treeSelection: {
      default: () => ({ item: null })
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
      isLoading: false,
      // A level arrives page by page: the last page asked for, and how many items
      // the source says the level holds in all.
      page: 0,
      childrenTotal: null
    };
  },

  computed: {
    hasLoadedChildren() {
      return Array.isArray(this.children) && this.children.length > 0;
    },

    // An item that knows the answer is believed — an entity says so before being
    // opened. One written by hand is judged on the children it came with.
    canOpen() {
      return this.item.hasChildren ?? this.hasLoadedChildren;
    },

    // Until something is clicked, the items themselves say which one is selected;
    // from the first click on, the tree does and the flags no longer apply.
    isSelected() {
      return this.treeSelection.item
        ? this.treeSelection.item === this.item
        : this.item.selected === true;
    },

    // What the level holds beyond what was loaded. Zero when the source gave no
    // total, which reads as nothing left to offer.
    remainingCount() {
      if (null === this.childrenTotal || !Array.isArray(this.children)) {
        return 0;
      }

      return Math.max(0, this.childrenTotal - this.children.length);
    },

    moreLabel() {
      return this.trans(
        'WexampleSymfonyDesignSystemBundle.vue.partials.tree-node::more.label',
        { '%count%': this.remainingCount }
      );
    },

    caretHtml() {
      return this.canOpen ? this.renderIcon('ph:bold/caret-right') : '';
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

    // Folding is the caret's business alone. A row that both opened and acted
    // would have no way to act on a directory without opening it too.
    async onCaretClick() {
      if (!this.canOpen) {
        return;
      }

      this.isOpen = !this.isOpen;

      if (!this.isOpen) {
        this.forgetChildren();
      } else if (this.children === null && this.treeLoadChildren) {
        await this.loadPage(0);
      }
    },

    onRowClick() {
      this.$emit('select', this.item);
    },

    // A closed branch keeps nothing: folding the root folds everything under it,
    // and reopening asks the source again, which is the only refresh the tree
    // needs. A node handed its children has no way to ask for them again, so it
    // holds on to them.
    forgetChildren() {
      if (!this.treeLoadChildren) {
        return;
      }

      this.children = null;
      this.page = 0;
      this.childrenTotal = null;
    },

    async loadPage(page) {
      this.isLoading = true;

      try {
        const level = await this.treeLoadChildren(this.item, page);

        this.children = page > 0 ? [...this.children, ...level.items] : level.items;
        this.childrenTotal = level.total ?? null;
        this.page = page;
      } finally {
        this.isLoading = false;
      }
    },

    async loadMore() {
      await this.loadPage(this.page + 1);
    }
  }
};
</script>
