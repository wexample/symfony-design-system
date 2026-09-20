<script>
import BaseField from '../../bases/form-field/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-submit-button',

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
