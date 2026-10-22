<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-tree-node-tree-node',

  inject: {
    treeRowComponents: {
      default: () => ({})
    },
    // Given a node, answers the level below it. Absent, a tree only shows the
    // children it was handed.
    treeLoadChildren: {
      default: null
    },
    // Held by the tree, read by every depth: what is selected spans the whole
    // thing, so no node can own that state.
    treeSelection: {
      default: () => ({ items: [], anchor: null })
    },
    // A range runs over the rows in the order they are drawn, which only the tree
    // can see: each node says which item its own row stands for.
    treeRegisterRow: {
      default: null
    },
    treeUnregisterRow: {
      default: null
    },
    treeItemKey: {
      default: null
    },
    treeIsRemembered: {
      default: null
    },
    treeIsOpen: {
      default: null
    },
    treeSetOpen: {
      default: null
    },
    treeRouteState: {
      default: () => ({ key: null, reveal: [] })
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
    },
    // The key of the node above, for a key built from the path of labels.
    parentKey: {
      type: String,
      default: null
    }
  },

  data() {
    const nodeKey = this.treeItemKey ? this.treeItemKey(this.item, this.parentKey) : null;

    return {
      nodeKey,
      // The item says how the node starts, the node owns it afterwards — that is
      // the whole difference with the Twig version, which cannot toggle. Once
      // the tree remembers, the memory wins over the item: a branch the visitor
      // folded stays folded even if the data arrives with `open: true`.
      // Above both, the address: a branch holding the item it names opens,
      // whatever was remembered, or that item could not be drawn.
      isOpen: (nodeKey !== null && this.treeRouteState.reveal.includes(nodeKey))
        || (nodeKey !== null && this.treeIsRemembered?.()
          ? this.treeIsOpen(nodeKey)
          : this.item.open === true),
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

    // Until something is clicked, the items themselves say which ones are
    // selected; from the first click on, the tree does and the flags no longer
    // apply.
    isSelected() {
      return this.treeSelection.items.length
        ? this.treeSelection.items.includes(this.item)
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
        'WexampleSymfonyDesignSystemBundle.components.tree-node.tree-node::more.label',
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
    isRevealed() {
      return this.nodeKey !== null && this.treeRouteState.reveal.includes(this.nodeKey);
    },

    label() {
      return this.item.label ?? this.item.name ?? '';
    }
  },

  watch: {
    // The back button asked for an item under this branch while it was drawn
    // closed: it opens the way a click on its caret would.
    isRevealed(revealed) {
      if (revealed && !this.isOpen) {
        this.onCaretClick();
      }
    }
  },

  async mounted() {
    this.treeRegisterRow?.(this.$refs.row, this.item, this.nodeKey);

    if (!this.isOpen) {
      return;
    }

    // Open on arrival without having been clicked: kept in mind, so the first
    // thing written down is what was on screen and not only what changed.
    this.treeSetOpen?.(this.nodeKey, true, false);

    // A branch reopened from memory has no children yet when they come on
    // demand: it asks for them the way a click would, and the branches below
    // do the same as they mount, one level at a time.
    if (this.children === null && this.treeLoadChildren) {
      await this.loadPage(0);
    }
  },

  beforeUnmount() {
    this.treeUnregisterRow?.(this.$refs.row, this.nodeKey);
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
      this.treeSetOpen?.(this.nodeKey, this.isOpen);

      if (!this.isOpen) {
        this.forgetChildren();
      } else if (this.children === null && this.treeLoadChildren) {
        await this.loadPage(0);
      }
    },

    // The keys travel as what they mean and not as what they are: the tree
    // decides whether it honours them, and on a mac the command key is ctrl.
    onRowClick(event) {
      this.$emit('select', {
        item: this.item,
        key: this.nodeKey,
        range: event.shiftKey,
        toggle: event.ctrlKey || event.metaKey
      });
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
