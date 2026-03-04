<script>
export default {
  props: {
    name: {
      type: String,
      default: ''
    },
    id: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: ''
    },
    errors: {
      type: Array,
      default: () => []
    },
    errorTranslationPrefix: {
      type: String,
      default: ''
    },
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    autocomplete: {
      type: String,
      default: ''
    },
    translate: {
      type: Boolean,
      default: true
    }
  },

  computed: {
    hasErrors() {
      return Array.isArray(this.errors) && this.errors.length > 0;
    },

    hasLabel() {
      return Boolean(this.label);
    },

    hasErrorTranslationPrefix() {
      return Boolean(this.errorTranslationPrefix);
    },

    resolvedId() {
      return this.id || this.name || null;
    }
  },

  methods: {
    resolveLabel(label) {
      if (!label) {
        return '';
      }

      return this.translate ? this.trans(label) : label;
    }
  }
};
</script>
