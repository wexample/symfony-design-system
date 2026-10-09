<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-checkbox-input-checkbox-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    // Frozen, it says its state in the words of the switch.
    frozenText() {
      return this.trans(`WexampleSymfonyDesignSystemBundle.common.system::frontend.switch.${this.modelValue ? 'checked' : 'unchecked'}`);
    }
  },

  methods: {
    onChange(event) {
      this.$emit('update:modelValue', event.target.checked);
    }
  }
};
</script>
