<script>
import BaseField from '../../bases/form-field/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-button-input',

  props: {
    label: {
      type: String,
      default: ''
    }
  },

  computed: {
    resolvedLabel() {
      return this.resolveLabel(this.label);
    }
  }
};
</script>
