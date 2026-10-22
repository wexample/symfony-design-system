<script>

// The twin of content-image.html.twig: a figure when there is something to say
// under the picture, a bare img when there is not. The caption is what decides,
// on both sides — a figure with an empty figcaption is not the same element.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-content-image-content-image',

  props: {
    src: {
      type: String,
      required: true
    },
    alt: {
      type: String,
      default: ''
    },
    caption: {
      type: String,
      default: null
    },
    loading: {
      type: String,
      default: 'lazy'
    },
    // Left null rather than guessed: an attribute the twig omits when the
    // caller says nothing must stay omitted here.
    width: {
      type: [String, Number],
      default: null
    },
    height: {
      type: [String, Number],
      default: null
    },
    extraClass: {
      type: String,
      default: null
    }
  }
};
</script>
