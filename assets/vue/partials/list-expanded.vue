<script>
import buildTranslatedBindings from "../../js/Helper/TranslationHelper";
import FocusableVueMixin from "../../js/Vue/FocusableVueMixin";
import KeyboardService from "@wexample/symfony-loader/js/Services/KeyboardService";

const translated = buildTranslatedBindings({
  resolvedSearchPlaceholder: [
    'searchPlaceholder',
    'WexampleSymfonyDesignSystemBundle.vue.partials.list-expanded::search.placeholder'
  ],
  resolvedFilterLabel: [
    'filterLabel',
    'WexampleSymfonyDesignSystemBundle.vue.partials.list-expanded::filter.label'
  ],
  resolvedEmptyLabel: [
    'emptyLabel',
    'WexampleSymfonyDesignSystemBundle.vue.partials.list-expanded::empty.label'
  ]
});

const ITEM_SELECTOR = '[data-list-expanded-item]';

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-list-expanded',
  mixins: [
    FocusableVueMixin
  ],

  props: {
    showHeader: {
      type: Boolean,
      default: false
    },
    showFilterButton: {
      type: Boolean,
      default: false
    },
    ...translated.props,
    hasItems: {
      type: Boolean,
      required: true
    },
    searchValue: {
      type: String,
      default: ''
    }
  },

  data() {
    return {
      activeItemIndex: -1
    };
  },

  mounted() {
    this.$nextTick(() => {
      this.syncFocusableItems();
    });
  },

  updated() {
    this.syncFocusableItems();
  },

  computed: {
    ...translated.computed
  },

  methods: {
    keyboardPriority() {
      return 10;
    },

    keyboardBindings() {
      return [
        {
          key: KeyboardService.KEY_ARROW_DOWN,
          callback: this.onArrowDownKey,
          options: {
            preventDefault: true,
            enabled: this.shouldHandleDirectionalNavigationKey
          }
        },
        {
          key: KeyboardService.KEY_ARROW_UP,
          callback: this.onArrowUpKey,
          options: {
            preventDefault: true,
            enabled: this.shouldHandleDirectionalNavigationKey
          }
        },
        {
          key: KeyboardService.KEY_HOME,
          callback: this.onHomeKey,
          options: {
            preventDefault: true,
            enabled: this.shouldHandleDirectionalNavigationKey
          }
        },
        {
          key: KeyboardService.KEY_END,
          callback: this.onEndKey,
          options: {
            preventDefault: true,
            enabled: this.shouldHandleDirectionalNavigationKey
          }
        },
        {
          key: KeyboardService.KEY_ENTER,
          callback: this.onEnterKey,
          options: {
            enabled: this.shouldHandleListActivationKey
          }
        }
      ];
    },

    getItemElements() {
      const container = this.$refs.itemsContainer;
      if (!container) {
        return [];
      }

      return Array.from(container.querySelectorAll(ITEM_SELECTOR));
    },

    syncFocusableItems() {
      const items = this.getItemElements();
      if (!items.length) {
        this.activeItemIndex = -1;
        return;
      }

      if (this.activeItemIndex < 0 || this.activeItemIndex >= items.length) {
        this.activeItemIndex = 0;
      }

      this.applyRovingTabIndex(items);
    },

    applyRovingTabIndex(items = this.getItemElements()) {
      for (let index = 0; index < items.length; index++) {
        items[index].setAttribute('tabindex', index === this.activeItemIndex ? '0' : '-1');
      }
    },

    getItemFromEvent(event) {
      if (!event.target || !(event.target instanceof Element)) {
        return null;
      }

      return event.target.closest(ITEM_SELECTOR);
    },

    focusItemAt(index) {
      const items = this.getItemElements();
      if (!items.length) {
        return false;
      }

      if (index < 0 || index >= items.length) {
        return false;
      }

      this.activeItemIndex = index;
      this.applyRovingTabIndex(items);
      items[index].focus();

      return true;
    },

    focusNextItem(step) {
      const items = this.getItemElements();
      if (!items.length) {
        return false;
      }

      let nextIndex = this.activeItemIndex;
      if (nextIndex < 0 || nextIndex >= items.length) {
        nextIndex = 0;
      } else {
        nextIndex += step;
      }

      nextIndex = Math.max(0, Math.min(nextIndex, items.length - 1));

      return this.focusItemAt(nextIndex);
    },

    focusFirstItem() {
      this.focusItemAt(0);
    },

    onSearchTab(event) {
      if (event.shiftKey) {
        return;
      }

      if (!this.hasItems) {
        return;
      }

      const firstItem = this.getItemElements()[0];
      if (!firstItem) {
        return;
      }

      event.preventDefault();
      this.focusFirstItem();
    },

    onItemFocus(event) {
      const item = this.getItemFromEvent(event);
      if (!item) {
        return;
      }

      const items = this.getItemElements();
      const index = items.indexOf(item);
      if (index < 0) {
        return;
      }

      this.activeItemIndex = index;
      this.applyRovingTabIndex(items);
    },

    isNaturallyFocusable(element) {
      const tagName = element.tagName.toLowerCase();
      return ['a', 'button', 'input', 'select', 'textarea', 'summary'].includes(tagName);
    },

    isTextInputElement(element) {
      if (!(element instanceof HTMLElement)) {
        return false;
      }

      const tagName = element.tagName.toLowerCase();
      if (tagName === 'textarea') {
        return true;
      }

      if (tagName === 'input') {
        const input = element;
        const type = (input.getAttribute('type') || 'text').toLowerCase();
        return type !== 'checkbox' && type !== 'radio' && type !== 'button' && type !== 'submit';
      }

      return element.isContentEditable;
    },

    isSearchInputElement(element) {
      return element instanceof HTMLElement && element === this.$refs.searchInput;
    },

    shouldHandleDirectionalNavigationKey(event) {
      const target = event.target;
      if (!(target instanceof HTMLElement)) {
        return false;
      }

      if (!this.isTextInputElement(target)) {
        return true;
      }

      return this.isSearchInputElement(target);
    },

    shouldHandleListActivationKey(event) {
      const target = event.target;
      if (!(target instanceof HTMLElement)) {
        return false;
      }

      return !this.isTextInputElement(target);
    },

    shouldHandleEnterActivation(element) {
      return !this.isNaturallyFocusable(element);
    },

    getActiveItem(event) {
      const directItem = this.getItemFromEvent(event);
      if (directItem) {
        return directItem;
      }

      const items = this.getItemElements();
      if (this.activeItemIndex >= 0 && this.activeItemIndex < items.length) {
        return items[this.activeItemIndex];
      }

      return null;
    },

    onArrowDownKey(event) {
      if (!this.hasItems) {
        return false;
      }

      if (this.isSearchInputElement(event?.target)) {
        return this.focusItemAt(0);
      }

      return this.focusNextItem(1);
    },

    onArrowUpKey(event) {
      if (!this.hasItems) {
        return false;
      }

      if (this.isSearchInputElement(event?.target)) {
        const items = this.getItemElements();
        if (!items.length) {
          return false;
        }

        return this.focusItemAt(items.length - 1);
      }

      return this.focusNextItem(-1);
    },

    onHomeKey(_event) {
      if (!this.hasItems) {
        return false;
      }

      return this.focusItemAt(0);
    },

    onEndKey(_event) {
      if (!this.hasItems) {
        return false;
      }

      const items = this.getItemElements();
      if (!items.length) {
        return false;
      }

      return this.focusItemAt(items.length - 1);
    },

    onEnterKey(event) {
      const item = this.getActiveItem(event);
      if (!item) {
        return false;
      }

      if (!this.shouldHandleEnterActivation(item)) {
        return false;
      }

      event.preventDefault();
      item.click();

      return true;
    },

    onSearchInput(event) {
      this.$emit('search', event.target.value);
    },
    onFilterClick() {
      this.$emit('filter-click');
    }
  }
};
</script>
