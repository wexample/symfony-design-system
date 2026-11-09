<script>
// The twin of cover.html.twig: the band across the head of a page — a
// picture, a video, or what the slot puts there (an animation) —, the glow
// of a placeholder while there is none, its words over its foot, its tools
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
    }
  }
};
</script>
