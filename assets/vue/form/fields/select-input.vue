<script>
import BaseField from './base-field.vue';

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
      return value === undefined || value === null ? '' : value;
    }
  }
};
</script>
