<script>

// The twin of toast.html.twig — the inline one. The .front shell beside it is
// the service's, and so is the closing: toast.ts runs an animation before it
// takes the element out. Inside a vue tree the parent owns the list, so closing
// is emitted rather than done, and the parent drops the item.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-toast-toast',

  emits: ['close'],

  props: {
    // Already translated, as every other vue component of the system takes it.
    title: {
      type: String,
      default: null
    },
    message: {
      type: String,
      default: null
    },
    // info, success, warning, error — anything else wears the neutral surface.
    type: {
      type: String,
      default: null
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    toastClass() {
      const classes = ['toast'];

      if (this.type) {
        classes.push(`toast--${this.type}`);
      }

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    },

    closeIconHtml() {
      return this.app.getServiceOrFail('icon').icon('ph:bold/x');
    }
  }
};
</script>
