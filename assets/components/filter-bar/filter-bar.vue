<script>
import ButtonMenu from '../button-menu/button-menu.vue';
import buildTranslatedBindings from '../../js/Helper/TranslationHelper';
import {
  filterClear,
  filterSelected,
  filterSummary,
  filterToggle
} from '../../js/Helper/FilterHelper';

const translated = buildTranslatedBindings({
  resolvedClearLabel: [
    'clearLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.filter.clear'
  ]
});

// A row of filters, one discreet menu each, saying what it narrows to once it
// does. It holds no rows: it produces a value — { key: value | [values] } —
// through v-model, for whoever uses it to ask an api, or for a table to apply
// to what it already has. The twin of filter-bar.html.twig, whose options are
// links changing the page's query instead.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-filter-bar-filter-bar',

  components: {
    ButtonMenu
  },

  props: {
    ...translated.props,
    // [{ key, label, options: [{ value, label, count }], multiple }]
    filters: {
      type: Array,
      default: () => []
    },
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },

  emits: ['update:modelValue', 'change'],

  computed: {
    ...translated.computed
  },

  methods: {
    isActive(filter) {
      return filterSelected(this.modelValue, filter.key).length > 0;
    },

    summary(filter) {
      return filterSummary(filter, this.modelValue);
    },

    // The options as toggles, and a way back to nothing once something is on.
    items(filter) {
      const selected = filterSelected(this.modelValue, filter.key);
      const options = filter.options.map((option) => ({
        label: option.label,
        value: String(option.value),
        count: option.count ?? null,
        checked: selected.includes(String(option.value))
      }));

      if (!selected.length) {
        return options;
      }

      return [
        ...options,
        { separator: true },
        { label: this.resolvedClearLabel, icon: 'ph:bold/x', clear: true }
      ];
    },

    onToggle(filter, { item }) {
      this.emitValue(filterToggle(this.modelValue, filter, item.value));
    },

    onSelect(filter, item) {
      if (item.clear) {
        this.emitValue(filterClear(this.modelValue, filter.key));
      }
    },

    emitValue(value) {
      this.$emit('update:modelValue', value);
      this.$emit('change', value);
    }
  }
};
</script>
