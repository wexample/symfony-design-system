<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-spinner',

  props: {
    size: {
      type: String,
      default: null
    },
    variant: {
      type: String,
      default: 'default'
    },
    align: {
      type: String,
      default: 'horizontal'
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
    wrapperClass() {
      const classes = ['spinner--wrapper', `spinner--align-${this.align}`];
      if (this.extraClass) {
        classes.push(this.extraClass);
      }
      return classes.join(' ');
    },

    spinnerClass() {
      const sizeClass = this.size ? `spinner--${this.size}` : null;
      const classes = ['spinner'];
      if (sizeClass) {
        classes.push(sizeClass);
      }
      if (this.variant && this.variant !== 'default') {
        classes.push(`spinner--${this.variant}`);
      }
      return classes.join(' ');
    },

    hasLabel() {
      return Boolean(this.label);
    }
  }
};
</script>
