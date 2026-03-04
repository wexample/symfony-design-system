<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-submit-button',

  props: {
    label: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'submit'
    },
    disabled: {
      type: Boolean,
      default: false
    },
    translate: {
      type: Boolean,
      default: true
    }
  },

  computed: {
    resolvedLabel() {
      if (!this.label) {
        return '';
      }

      return this.translate ? this.trans(this.label) : this.label;
    }
  }
};
</script>
