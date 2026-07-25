<script>
export default {
  inject: {
    formController: { default: null }
  },

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

  data() {
    return {
      controllerDisabled: false,
      controllerErrors: [],
    };
  },

  computed: {
    isDisabled() {
      return this.disabled || this.controllerDisabled;
    },

    resolvedErrors() {
      const propErrors = Array.isArray(this.errors) ? this.errors : [];
      const ctrlErrors = Array.isArray(this.controllerErrors) ? this.controllerErrors : [];
      return [...propErrors, ...ctrlErrors];
    },

    hasErrors() {
      return this.resolvedErrors.length > 0;
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

  mounted() {
    if (this.formController && this.name) {
      this._fieldProxy = {
        fieldName: this.name,
        disable: () => { this.controllerDisabled = true; },
        enable: () => { this.controllerDisabled = false; },
        setErrors: (errors) => { this.controllerErrors = [...errors]; },
        clearErrors: () => { this.controllerErrors = []; },
      };
      this.formController.registerField(this._fieldProxy);
    }
  },

  beforeUnmount() {
    if (this._fieldProxy) {
      this.formController?.unregisterField(this._fieldProxy);
      this._fieldProxy = null;
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
