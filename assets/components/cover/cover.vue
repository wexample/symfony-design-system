<script>
// The twin of cover.html.twig: the band across the head of a page — a
// picture, a video, or what the slot puts there (an animation) —, the missing
// picture while there is none (`placeholder`), alive while one is made (`pending`), its words over its foot, its tools
// over its top end corner (slot `float`).
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-cover-cover',

  props: {
    src: {
      type: String,
      default: null
    },
    video: {
      type: String,
      default: null
    },
    alt: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: null
    },
    subtitle: {
      type: String,
      default: null
    },
    height: {
      type: String,
      default: null
    },
    // One is being made: the band says so, alive, over what it holds; true,
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
