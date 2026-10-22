import KeyboardService from "@wexample/symfony-loader/js/Services/KeyboardService";
import WithDomManipulationVueMixin from "./WithDomManipulationVueMixin";

export default {
  mixins: [
    WithDomManipulationVueMixin
  ],

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

  methods: {
    rovingItemSelector() {
      return "[data-list-expanded-item]";
    },

    rovingItemsContainerRef() {
      return "itemsContainer";
    },

    rovingSearchInputRef() {
      return "searchInput";
    },

    rovingKeyboardPriority() {
      return 10;
    },

    rovingHasItems() {
      return this.hasItems === true;
    },

    rovingShouldHandleEnterActivation(element) {
      return !this.domIsNaturallyFocusable(element);
    },

    keyboardPriority() {
      return this.rovingKeyboardPriority();
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
      const containerRef = this.rovingItemsContainerRef();
      const container = this.$refs[containerRef];
      if (!(container instanceof HTMLElement)) {
        return [];
      }

      return Array.from(container.querySelectorAll(this.rovingItemSelector()));
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
        items[index].setAttribute("tabindex", index === this.activeItemIndex ? "0" : "-1");
      }
    },

    getItemFromEvent(event) {
      const target = this.domGetEventTargetElement(event);
      return target ? target.closest(this.rovingItemSelector()) : null;
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
      return this.focusItemAt(0);
    },

    getSearchInputElement() {
      const searchInputRef = this.rovingSearchInputRef();
      const searchInput = this.$refs[searchInputRef];
      return searchInput instanceof HTMLElement ? searchInput : null;
    },

    isSearchInputElement(element) {
      return this.domIsSameElement(element, this.getSearchInputElement());
    },

    shouldHandleDirectionalNavigationKey(event) {
      const target = this.domGetEventTargetElement(event);
      if (!target) {
        return false;
      }

      if (!this.domIsTextInputElement(target)) {
        return true;
      }

      return this.isSearchInputElement(target);
    },

    shouldHandleListActivationKey(event) {
      return !this.domIsTextInputElement(this.domGetEventTargetElement(event));
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

    onSearchTab(event) {
      if (event.shiftKey || !this.rovingHasItems()) {
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

    onArrowDownKey(event) {
      if (!this.rovingHasItems()) {
        return false;
      }

      if (this.isSearchInputElement(event?.target)) {
        return this.focusItemAt(0);
      }

      return this.focusNextItem(1);
    },

    onArrowUpKey(event) {
      if (!this.rovingHasItems()) {
        return false;
      }

      if (this.isSearchInputElement(event?.target)) {
        const items = this.getItemElements();
        if (!items.length) {
          return false;
        }

        return this.focusItemAt(items.length - 1);
      }

      const items = this.getItemElements();
      if (!items.length) {
        return false;
      }

      if (this.activeItemIndex <= 0) {
        const searchInput = this.getSearchInputElement();
        if (searchInput) {
          searchInput.focus();
          return true;
        }

        return false;
      }

      return this.focusNextItem(-1);
    },

    onHomeKey() {
      if (!this.rovingHasItems()) {
        return false;
      }

      return this.focusItemAt(0);
    },

    onEndKey() {
      if (!this.rovingHasItems()) {
        return false;
      }

      const items = this.getItemElements();
      if (!items.length) {
        return false;
      }

      return this.focusItemAt(items.length - 1);
    },

    activateItemByKeyboard(item, event) {
      event.preventDefault();
      item.click();
      return true;
    },

    onEnterKey(event) {
      const item = this.getActiveItem(event);
      if (!item) {
        return false;
      }

      if (!this.rovingShouldHandleEnterActivation(item)) {
        return false;
      }

      return this.activateItemByKeyboard(item, event);
    }
  }
};
