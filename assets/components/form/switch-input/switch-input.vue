<script>
import BaseField from '../../bases/form-field/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-switch-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Boolean,
      default: false
    }
  },

  methods: {
    onChange(event) {
      this.$emit('update:modelValue', event.target.checked);
    }
  }
};
</script>
