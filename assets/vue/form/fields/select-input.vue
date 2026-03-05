<script>
import BaseField from '../../bases/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-select-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    options: {
      type: Array,
      default: () => []
    }
  },

  computed: {
    resolvedValue() {
      const current = this.modelValue === undefined || this.modelValue === null
        ? ''
        : String(this.modelValue);
      const hasCurrent = this.options.some((option) => this.resolveOptionValue(option) === current);

      if (hasCurrent) {
        return current;
      }

      if (!this.options.length) {
        return current;
      }

      return this.resolveOptionValue(this.options[0]);
    }
  },

  methods: {
    onChange(event) {
      this.$emit('update:modelValue', event?.target?.value ?? '');
    },

    resolveOptionLabel(option) {
      const label = option?.label ?? '';
      return this.resolveLabel(label);
    },

    resolveOptionValue(option) {
      const value = option?.value;
      return value === undefined || value === null ? '' : String(value);
    }
  }
};
</script>
