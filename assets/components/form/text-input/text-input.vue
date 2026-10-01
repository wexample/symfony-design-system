<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-text-input-text-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    placeholder: {
      type: String,
      default: ''
    },
    // A fixed word beside the value — a unit, a currency — shown in the
    // field's frame, read with the field, never part of what is emitted.
    prefix: {
      type: String,
      default: ''
    },
    suffix: {
      type: String,
      default: ''
    }
  },

  computed: {
    hasAddon() {
      return Boolean(this.prefix || this.suffix);
    },

    addonDescribedBy() {
      const ids = [
        this.prefix ? `${this.resolvedId}-prefix` : null,
        this.suffix ? `${this.resolvedId}-suffix` : null
      ].filter(Boolean);

      return ids.length ? ids.join(' ') : null;
    }
  },

  methods: {
    onInput(event) {
      this.$emit('update:modelValue', event?.target?.value ?? '');
    }
  }
};
</script>
