<script>
import buildTranslatedBindings from "../../js/Helper/TranslationHelper";
import FocusableVueMixin from "../../js/Vue/FocusableVueMixin";
import WithRovingFocusKeyboardVueMixin from "../../js/Vue/WithRovingFocusKeyboardVueMixin";

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

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-list-expanded',
  mixins: [
    FocusableVueMixin,
    WithRovingFocusKeyboardVueMixin
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

  computed: {
    ...translated.computed
  },

  methods: {
    onSearchInput(event) {
      this.$emit('search', event.target.value);
    },
    onFilterClick() {
      this.$emit('filter-click');
    }
  }
};
</script>
