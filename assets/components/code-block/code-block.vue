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
    },
    // Only the first lines, an ellipsis under them when there were more: a
    // glimpse of the code rather than the whole of it.
    maxLines: {
      type: Number,
      default: null
    }
  },

  computed: {
    shown() {
      const lines = this.code.split('\n');

      if (!this.maxLines || lines.length <= this.maxLines) {
        return this.code;
      }

      return [...lines.slice(0, this.maxLines), '…'].join('\n');
    },

    html() {
      return codeHighlight(this.shown, this.lang);
    }
  }
};
</script>
