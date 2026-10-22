<script>

// The twin of button-link.html.twig: the same anchor wearing the button style.
// It carries no behaviour of its own — following the link is the browser's job,
// which is the whole reason this is not a button.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-button-link-button-link',

  props: {
    href: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: null
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  }
};
</script>
