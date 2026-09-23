<script>
import ZoneResizer from '../zone-resizer/zone-resizer.vue';

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-collapsible-zone-collapsible',

  components: {
    ZoneResizer
  },

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
    },
    // A handle in the gap on one of its edges, for a region the visitor is
    // allowed to size. It goes with the fold rather than against it: folded,
    // there is nothing left to size.
    resizable: {
      type: Boolean,
      default: false
    },
    resizerEdge: {
      type: String,
      default: 'end'
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
