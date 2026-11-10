<script>

// The twin of media.html.twig: a picture in the frame of the media shape, a
// link when it has somewhere to lead, the missing picture where there is
// none and its ground while it loads (`placeholder`), alive while one is
// made (`pending`), and a caption over its foot — given as text, or as the
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
    },
    // One is being made: the frame says so, alive, over what it holds; true,
    // or the words to say it with.
    pending: {
      type: [Boolean, String],
      default: false
    }
  },

  computed: {
    pendingLabel() {
      return typeof this.pending === 'string' && this.pending
        ? this.pending
        : this.trans('WexampleSymfonyDesignSystemBundle.common.system::frontend.placeholder.pending');
    }
  }
};
</script>
