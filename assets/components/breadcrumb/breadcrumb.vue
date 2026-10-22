<script>

// The twin of breadcrumb.html.twig. The twig side is handed routes and resolves
// each label out of the target page's catalogue; here the items arrive already
// resolved, the way tab.vue takes its own. That is the division the whole system
// works on: the server knows the route table, so the server answers it, and what
// crosses to the client is the answer rather than the question.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-breadcrumb-breadcrumb',

  props: {
    // Each item is { label, href, current }: no href, or current, makes the
    // plain span the twig draws for the page being read.
    items: {
      type: Array,
      required: true
    },
    separatorIcon: {
      type: String,
      default: 'ph:bold/caret-right'
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    separatorHtml() {
      return this.app.getServiceOrFail('icon').icon(this.separatorIcon);
    }
  },

  methods: {
    // The last one is where you are, whether or not it says so.
    isCurrent(item, index) {
      return item.current === true || index === this.items.length - 1;
    }
  }
};
</script>
