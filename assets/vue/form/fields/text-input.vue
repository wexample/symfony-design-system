<script>
import BaseField from './base-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-text-input',

  props: {
    value: {
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
    }
  },

  methods: {
    onInput(event) {
      this.$emit('input', event?.target?.value ?? '');
    }
  }
};
</script>
