<script>
import { codeHighlight } from '../../js/Helper/CodeHighlightHelper';

// The twin of code-block.html.twig. The twig runs the code through `|e`; here
// the highlighter escapes it before colouring it, which is the same promise
// kept differently. Coloured client-side only, for the languages the
// highlighter knows (CodeHighlightHelper).
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-code-block-code-block',

  props: {
    code: {
      type: String,
      required: true
    },
    lang: {
      type: String,
      default: 'text'
    }
  },

  computed: {
    html() {
      return codeHighlight(this.code, this.lang);
    }
  }
};
</script>
