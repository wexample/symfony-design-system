<script>
// The twin of credit.html.twig: what a work shown on the page owes whoever
// made it — who, under which licence, found where —, each part a link where
// it has an address, the parts it lacks left out.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-credit-credit',

  props: {
    // What is credited: « Portrait ».
    label: {
      type: String,
      default: null
    },
    author: {
      type: String,
      default: null
    },
    license: {
      type: String,
      default: null
    },
    licenseUrl: {
      type: String,
      default: null
    },
    source: {
      type: String,
      default: null
    },
    sourceUrl: {
      type: String,
      default: null
    }
  },

  computed: {
    parts() {
      return [
        { label: this.author, href: null },
        { label: this.license, href: this.licenseUrl },
        { label: this.source, href: this.sourceUrl }
      ].filter((part) => part.label);
    }
  }
};
</script>
