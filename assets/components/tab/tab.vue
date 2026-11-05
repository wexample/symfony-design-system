<script>
import { tabsFit, tabsRevealActive, tabsScrollBy, tabsWatch } from '../../js/Helper/TabsHelper';

// A bar of tabs fits the width it is given: its words while they fit, its
// icons alone where every tab has one, past that the bar scrolled between two
// arrows — the same as the server bar (tab.ts), the logic written once
// (TabsHelper).
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-tab-tab',

  // `app` is a global property of the vue application, not a prop: declaring it
  // here left it undefined for every parent that did not pass it by hand, and
  // getItemIcon() reads a service off it.
  props: {
    items: {
      type: Array,
      required: true,
      default: () => []
    },
    activeKey: {
      type: String,
      default: ''
    },
    fill: {
      type: Boolean,
      default: false
    },
    compact: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    resolvedItems() {
      return this.items.filter((item) => item && item.visible !== false);
    },

    resolvedTabsClass() {
      return {
        'tabs--fill': this.fill,
        'tabs--compact': this.compact
      };
    }
  },

  watch: {
    activeKey() {
      this.$nextTick(() => tabsRevealActive(this.$el));
    },

    // Other tabs, other words: the bar laid out again.
    resolvedItems() {
      this.$nextTick(() => tabsFit(this.$el));
    }
  },

  mounted() {
    this.unwatchFit = tabsWatch(this.$el);
  },

  beforeUnmount() {
    this.unwatchFit?.();
  },

  methods: {
    scrollTabs(direction) {
      tabsScrollBy(this.$el, direction);
    },

    isActive(item) {
      return item.key === this.activeKey;
    },

    onClickTab(event, item) {
      event.preventDefault();

      if (!item || !item.key || item.disabled) {
        return;
      }

      if (this.isActive(item)) {
        return;
      }

      this.$emit('change', item.key, item);
    },

    getItemIcon(item) {
      if (!item?.icon) {
        return '';
      }

      const iconService = this.app.getServiceOrFail('icon');
      return iconService.icon(item.icon);
    }
  }
};
</script>
