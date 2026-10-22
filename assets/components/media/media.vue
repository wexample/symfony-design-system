<script>

// The twin of media.html.twig: a picture in the frame of the media shape, a
// link when it has somewhere to lead, its placeholder glow until it loads or
// when there is none, and a caption over its foot — given as text, or as the
// `caption` slot for more than text. A class or a style set on it lands on
// the frame, which is how a wall hangs it.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-media-media',

  props: {
    src: {
      type: String,
      default: null
    },
    srcset: {
      type: String,
      default: null
    },
    sizes: {
      type: String,
      default: null
    },
    alt: {
      type: String,
      default: ''
    },
    // The picture's own, reserved by the browser before it loads.
    width: {
      type: [String, Number],
      default: null
    },
    height: {
      type: [String, Number],
      default: null
    },
    href: {
      type: String,
      default: null
    },
    caption: {
      type: String,
      default: null
    },
    loading: {
      type: String,
      default: 'lazy'
    }
  }
};
</script>
