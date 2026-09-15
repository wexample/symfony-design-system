<script>
import {
  assistanceWriteText,
} from '@wexample/js-api/Helper/Assistance';

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
    help: {
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
      // Whether something other than the person is holding the field. Always
      // temporary: a field left assisted is a field nobody can use.
      isAssisted: false,
      assistanceAbort: null,
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
      // Held in a variable: inside the object below `this` is the proxy, not the
      // component it stands for.
      const field = this;

      this._fieldProxy = {
        fieldName: this.name,
        disable: () => { this.controllerDisabled = true; },
        enable: () => { this.controllerDisabled = false; },
        setErrors: (errors) => { this.controllerErrors = [...errors]; },
        clearErrors: () => { this.controllerErrors = []; },
        get isAssisted() { return field.isAssisted; },
        assistanceActivate: () => this.assistanceActivate(),
        assistanceDeactivate: () => this.assistanceDeactivate(),
        setValueAssisted: (value, options) => this.setValueAssisted(value, options),
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
    // Hands the field over. The group goes inert rather than each control being
    // locked one by one: what is being written is not to be argued with.
    assistanceActivate() {
      this.isAssisted = true;
    },

    assistanceDeactivate() {
      // Whatever was still being written lands on its value at once.
      this.assistanceAbort?.abort();
      this.assistanceAbort = null;
      this.isAssisted = false;
    },

    async setValueAssisted(value, options = {}) {
      this.assistanceActivate();

      const controller = new AbortController();
      this.assistanceAbort = controller;

      try {
        await this.writeValueAssisted(value, { ...options, signal: controller.signal });
      } finally {
        this.assistanceDeactivate();
      }
    },

    /**
     * How this field spells a value out. A value that is written is spelled
     * character by character; a field whose value is not a word — a switch, a
     * set of radios — overrides this and says so its own way.
     */
    async writeValueAssisted(value, options) {
      await assistanceWriteText(
        (written) => this.$emit('update:modelValue', written),
        String(value ?? ''),
        options
      );
    },

    resolveLabel(label) {
      if (!label) {
        return '';
      }

      return this.translate ? this.trans(label) : label;
    }
  }
};
</script>
