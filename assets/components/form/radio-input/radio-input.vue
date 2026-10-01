<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-radio-input-radio-input',
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
    frozenText() {
      const option = this.options.find((entry) => this.resolveOptionValue(entry) === this.modelValue);

      return option ? this.resolveOptionLabel(option) : '';
    }
  },

  methods: {
    onChange(value) {
      this.$emit('update:modelValue', value);
    },

    resolveOptionLabel(option) {
      const label = option?.label ?? '';
      return this.resolveLabel(label);
    },

    resolveOptionValue(option) {
      const value = option?.value;
      return value === undefined || value === null ? '' : String(value);
    },

    resolveOptionId(index) {
      return (this.resolvedId || 'radio') + '_' + index;
    }
  }
};
</script>
