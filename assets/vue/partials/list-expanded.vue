<script>
import buildTranslatedBindings from "../../js/Helper/TranslationHelper";

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

  computed: {
    ...translated.computed
  },

  methods: {
    getItemElements() {
      const container = this.$refs.itemsContainer;
      if (!container) {
        return [];
      }

      return Array.from(container.querySelectorAll(ITEM_SELECTOR));
    },

    focusFirstItem() {
      const firstItem = this.getItemElements()[0];
      if (!firstItem) {
        return;
      }

      if (!this.isNaturallyFocusable(firstItem) && !firstItem.hasAttribute('tabindex')) {
        firstItem.setAttribute('tabindex', '-1');
      }

      firstItem.focus();
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

    isNaturallyFocusable(element) {
      const tagName = element.tagName.toLowerCase();
      return ['a', 'button', 'input', 'select', 'textarea', 'summary'].includes(tagName);
    },

    shouldHandleEnterActivation(element) {
      return !this.isNaturallyFocusable(element);
    },

    onRootKeydown(event) {
      if (event.key !== 'Enter') {
        return;
      }

      const target = event.target;
      if (!target) {
        return;
      }

      const item = target.closest(ITEM_SELECTOR);
      if (!item) {
        return;
      }

      if (!this.shouldHandleEnterActivation(item)) {
        return;
      }

      event.preventDefault();
      item.click();
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
