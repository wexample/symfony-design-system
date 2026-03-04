<script>
import BaseField from '../../bases/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-submit-button',

  props: {
    type: {
      type: String,
      default: 'submit'
    }
  },

  computed: {
    resolvedLabel() {
      return this.resolveLabel(this.label);
    }
  }
};
</script>
