<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-text-input',

  props: {
    value: {
      type: String,
      default: ''
    },
    name: {
      type: String,
      default: ''
    },
    id: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
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
    onInput(event) {
      this.$emit('input', event?.target?.value ?? '');
    }
  }
};
</script>
