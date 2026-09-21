<script>

// The twin of document-embed.html.twig. The wrapper classes the twig side
// builds in DocumentExtension are built here from the same two options, so a
// frame written by vue sits in its parent exactly as a rendered one does.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-document-embed-document-embed',

  props: {
    src: {
      type: String,
      required: true
    },
    title: {
      type: String,
      default: ''
    },
    // '16-9', '4-3'… Without one the box has no height of its own, so it takes
    // the one its parent gives.
    ratio: {
      type: String,
      default: null
    },
    loading: {
      type: String,
      default: 'lazy'
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    frameClass() {
      const classes = ['media', this.ratio ? `media--${this.ratio}` : 'media--fill'];

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    }
  }
};
</script>
