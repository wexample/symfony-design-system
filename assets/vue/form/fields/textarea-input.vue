<script>
import BaseField from '../../bases/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-textarea-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    rows: {
      type: Number,
      default: 4
    },
    placeholder: {
      type: String,
      default: ''
    }
  },

  methods: {
    onInput(event) {
      this.$emit('update:modelValue', event?.target?.value ?? '');
    }
  }
};
</script>
