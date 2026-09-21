<script>

// The twin of document-embed.html.twig.
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
    loading: {
      type: String,
      default: 'lazy'
    },
    extraClass: {
      type: String,
      default: null
    }
  }
};
</script>
