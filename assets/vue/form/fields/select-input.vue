<script>
import BaseField from '../../bases/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-select-input',

  props: {
    value: {
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
      const current = this.value === undefined || this.value === null
        ? ''
        : String(this.value);
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
      this.$emit('input', event?.target?.value ?? '');
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
