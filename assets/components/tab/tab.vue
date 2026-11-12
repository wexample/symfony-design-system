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

    // The arrows move from a tab to the next, the ends reached with Home and
    // End: the tab reached is shown, as a list of tabs answers the keyboard.
    onKeydown(event) {
      const steps = { ArrowRight: 1, ArrowLeft: -1, Home: 'first', End: 'last' };
      const step = steps[event.key];
      const items = this.resolvedItems.filter((item) => !item.disabled);

      if (!step || !items.length || !event.target.closest?.('.tabs--item')) {
        return;
      }

      event.preventDefault();

      const current = items.findIndex((item) => this.isActive(item));
      const next = step === 'first' ? 0
        : step === 'last' ? items.length - 1
          : (current + step + items.length) % items.length;

      if (items[next] && !this.isActive(items[next])) {
        // The tab reached takes the focus itself: whoever holds the bar may
        // show its view only once it has what the view needs.
        this.$el.querySelectorAll(':scope > .tabs--item')[this.resolvedItems.indexOf(items[next])]?.focus();
        this.$emit('change', items[next].key, items[next]);
      }
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
