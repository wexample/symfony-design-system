<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-bar-bar',

  props: {
    // Owed unless the `body` slot says what the line is about instead.
    title: {
      type: String,
      default: null
    },
    subtitle: {
      type: String,
      default: null
    },
    icon: {
      type: String,
      default: null
    },
    // Markup, for the cases an icon name cannot say: a real avatar, an image,
    // a status circle. Rendered as given, like the Twig slot of the same name.
    avatar: {
      type: String,
      default: null
    },
    href: {
      type: String,
      default: null
    },
    // A line one picks among others — a list's current one —: a button, the
    // one picked pressed. Null, the default, is a line that is only read.
    pressed: {
      type: Boolean,
      default: null
    }
  },

  computed: {
    // A bar standing for something reachable is a link over its whole width;
    // one picked in place, a button.
    tag() {
      if (this.href) {
        return 'a';
      }

      return this.pressed === null ? 'div' : 'button';
    },

    pressedAttributes() {
      return this.tag === 'button'
        ? { type: 'button', 'aria-pressed': this.pressed ? 'true' : 'false' }
        : {};
    },

    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  }
};
</script>
