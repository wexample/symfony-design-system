<script>

// The twin of menu-item-link.html.twig. Unlike the other menu rows it takes its
// icon and its label as options rather than reading them off a route, so the
// only thing it owed the server was the current path — and the browser has that
// one. An item is the page being read when it leads to it; a section spanning
// several addresses says so itself, since only the caller knows which pages are
// the same place.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-menu-item-link-menu-item-link',

  props: {
    href: {
      type: String,
      required: true
    },
    // Already translated, as every other vue component of the system takes it.
    label: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: null
    },
    // How many of whatever the item leads to, at the far end.
    count: {
      type: [String, Number],
      default: null
    },
    // Left null to let the current path decide.
    active: {
      type: Boolean,
      default: null
    },
    newWindow: {
      type: Boolean,
      default: false
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    isActive() {
      return this.active === null
        ? this.href === window.location.pathname
        : this.active;
    },

    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  }
};
</script>
