<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-button-input-button-input',

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
