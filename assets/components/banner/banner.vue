<script>

// The twin of banner.html.twig — the inline one, the banner a page writes for
// itself. The .front shell beside it stays the service's: what that one shows
// arrives after the page is drawn, which is the whole reason it is a shell.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-banner-banner',

  props: {
    // Already translated, as every other vue component of the system takes it.
    message: {
      type: String,
      default: null
    },
    // info, success, warning, error — anything else wears the neutral surface.
    type: {
      type: String,
      default: null
    },
    // Pinned to the top of the viewport rather than sitting in the flow.
    floating: {
      type: Boolean,
      default: false
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    bannerClass() {
      const classes = ['banner'];

      if (this.type) {
        classes.push(`banner--${this.type}`);
      }

      if (this.floating) {
        classes.push('banner--floating');
      }

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    }
  }
};
</script>
