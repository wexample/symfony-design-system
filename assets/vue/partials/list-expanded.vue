<script>
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
    searchPlaceholder: {
      type: String,
      required: true
    },
    filterLabel: {
      type: String,
      required: true
    },
    emptyLabel: {
      type: String,
      required: true
    },
    hasItems: {
      type: Boolean,
      required: true
    },
    searchValue: {
      type: String,
      default: ''
    }
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
