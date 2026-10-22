<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-submit-input-submit-input',

  props: {
    type: {
      type: String,
      default: 'submit'
    },
    icon: {
      type: String,
      default: ''
    },
    primary: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      thisIsSubmitting: false,
    };
  },

  computed: {
    resolvedLabel() {
      return this.resolveLabel(this.label);
    },

    isSubmitting() {
      return this.thisIsSubmitting;
    }
  },

  watch: {
    'formController.isSubmitting'(val) {
      if (!val) {
        this.thisIsSubmitting = false;
      }
    }
  },

  methods: {
    onButtonClick() {
      if (this.type === 'submit') {
        this.thisIsSubmitting = true;
      }
    }
  }
};
</script>
