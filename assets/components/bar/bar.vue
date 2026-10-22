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
    }
  },

  computed: {
    // A bar standing for something reachable is a link over its whole width.
    tag() {
      return this.href ? 'a' : 'div';
    },

    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  }
};
</script>
