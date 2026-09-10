<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-dots',

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
      const classes = ['dots--wrapper', `dots--align-${this.align}`];
      if (this.extraClass) {
        classes.push(this.extraClass);
      }
      return classes.join(' ');
    },

    dotsClass() {
      const classes = ['dots'];
      if (this.size) {
        classes.push(`dots--${this.size}`);
      }
      if (this.variant && this.variant !== 'default') {
        classes.push(`dots--${this.variant}`);
      }
      return classes.join(' ');
    },

    hasLabel() {
      return Boolean(this.label);
    }
  }
};
</script>
