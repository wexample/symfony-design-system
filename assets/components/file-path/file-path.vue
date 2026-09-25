<script>
import { filePathSplit } from '../../js/Helper/FilePathHelper';

// The twin of file-path.html.twig.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-file-path-file-path',

  props: {
    path: {
      type: String,
      default: ''
    }
  },

  computed: {
    parts() {
      return filePathSplit(this.path);
    }
  }
};
</script>
