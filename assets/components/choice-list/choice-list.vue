<script>
import { rippleAgentPress } from '../../js/Helper/RippleHelper';

// Where the list's words are, shared with choice-list.html.twig.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.choice_list.';

// The twin of choice-list.html.twig. Server-side the choice is choice-list.ts
// setting `aria-checked`; here it is state, usable with v-model and still
// working when the parent binds nothing.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-choice-list-choice-list',

  emits: ['update:modelValue', 'change'],

  props: {
    // [{ value, label, icon, description, disabled, group }]: items of a
    // `group` follow one another, under its name.
    items: {
      type: Array,
      required: true
    },
    modelValue: {
      type: [String, Number],
      default: undefined
    },
    // The one chosen at first, when the parent binds no v-model.
    value: {
      type: [String, Number],
      default: null
    },
    // From how many options a field narrows them; never, by default.
    filterFrom: {
      type: Number,
      default: 0
    },
    // What names the list for whoever reads the page aloud.
    label: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      innerValue: this.value,
      filterQuery: ''
    };
  },

  computed: {
    current() {
      return this.modelValue === undefined ? this.innerValue : this.modelValue;
    },

    filterable() {
      return this.filterFrom > 0 && this.items.length >= this.filterFrom;
    },

    shownItems() {
      const query = this.filterQuery.trim().toLowerCase();

      if (!query) {
        return this.items;
      }

      return this.items.filter((item) => `${item.label ?? ''} ${item.description ?? ''}`.toLowerCase().includes(query));
    },

    // Each item shown, with the name of its group when it opens one.
    shownRows() {
      return this.shownItems.map((item, index) => ({
        item,
        heading: item.group && item.group !== this.shownItems[index - 1]?.group ? item.group : null
      }));
    },

    filterLabel() {
      return this.trans(`${DOMAIN}filter`);
    },

    emptyLabel() {
      return this.trans(`${DOMAIN}filter_empty`);
    },

    searchIconHtml() {
      return this.renderIcon('ph:bold/magnifying-glass');
    },

    checkIconHtml() {
      return this.renderIcon('ph:bold/check');
    }
  },

  methods: {
    isChosen(item) {
      return this.current !== null && this.current !== undefined && item.value === this.current;
    },

    choose(item) {
      if (item.disabled) {
        return;
      }

      this.innerValue = item.value;
      this.$emit('update:modelValue', item.value);
      this.$emit('change', item.value);
    },

    // Found by its value, or by its name as the reader sees it.
    findItem(wanted) {
      const name = String(wanted ?? '').trim().toLowerCase();

      return this.items.find((item) => String(item.value) === String(wanted))
        ?? this.items.find((item) => String(item.label ?? '').toLowerCase() === name)
        ?? null;
    },

    // What an agent reads and does (`choice-list.ai.yml`, shared with
    // choice-list.ts): the same as a press.
    getAgentLabel() {
      return this.label || undefined;
    },

    getAgentState() {
      return { value: this.current ?? null };
    },

    getAgentActions() {
      return {
        items: () => ({
          items: this.items.map((item) => ({
            value: item.value,
            item: item.label,
            chosen: this.isChosen(item),
            disabled: item.disabled || undefined
          }))
        }),
        select: async ({ item }) => {
          const found = this.findItem(item);

          if (!found) {
            return { error: `No option "${item}" in this list.` };
          }

          if (found.disabled) {
            return { error: `The option "${item}" cannot be chosen.` };
          }

          this.filterQuery = '';
          await this.$nextTick();
          const index = this.items.indexOf(found);
          await rippleAgentPress(this.app, this.$el.querySelectorAll('.choice-list--choice')[index] ?? null);
          this.choose(found);
          await this.$nextTick();

          return { value: this.current ?? null };
        }
      };
    },

    renderIcon(name) {
      return name ? this.app.getServiceOrFail('icon').icon(name) : '';
    }
  }
};
</script>
