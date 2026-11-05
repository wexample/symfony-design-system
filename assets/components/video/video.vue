<script>
import { videoEmbedUrl } from '../../js/Helper/VideoHelper';

// The twin of video.html.twig: a host's page played by the host's player,
// embedded; a file played by the browser; the same frame and caption.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-video-video',

  props: {
    src: {
      type: String,
      required: true
    },
    title: {
      type: String,
      default: null
    },
    caption: {
      type: String,
      default: null
    },
    poster: {
      type: String,
      default: null
    }
  },

  computed: {
    embed() {
      return videoEmbedUrl(this.src);
    }
  }
};
</script>
