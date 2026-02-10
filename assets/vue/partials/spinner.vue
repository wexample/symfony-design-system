<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-spinner',

  props: {
    size: {
      type: String,
      default: 'md'
    },
    variant: {
      type: String,
      default: 'default'
    },
    label: {
      type: String,
      default: ''
    },
    extraClass: {
      type: String,
      default: ''
    }
  },

  computed: {
    spinnerClass() {
      const classes = ['spinner', `spinner--${this.size}`];
      if (this.variant && this.variant !== 'default') {
        classes.push(`spinner--${this.variant}`);
      }
      if (this.extraClass) {
        classes.push(this.extraClass);
      }
      return classes.join(' ');
    },

    hasLabel() {
      return Boolean(this.label);
    }
  }
};
</script>
