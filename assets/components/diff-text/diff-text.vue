<script>
import { diffTextRows, diffTextSideBySide, diffTextStats } from '../../js/Helper/DiffTextHelper';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.diff_text.';

// The twin of diff-text.html.twig: two versions of a text compared line by
// line in the browser — a version against the draft, without a reload —,
// the rows DiffTextHelper's as the server's are. A fold is opened by a press.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-diff-text-diff-text',

  props: {
    before: {
      type: String,
      default: ''
    },
    after: {
      type: String,
      default: ''
    },
    // `unified` — one column —, or `split`, side by side.
    mode: {
      type: String,
      default: 'unified'
    },
    // The lines kept around each change; the rest folded. Below zero, none.
    context: {
      type: Number,
      default: 3
    },
    words: {
      type: Boolean,
      default: true
    },
    beforeLabel: {
      type: String,
      default: null
    },
    afterLabel: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      // The folds opened, by where they start.
      opened: {}
    };
  },

  computed: {
    rows() {
      return diffTextRows(this.before, this.after, this.context, this.words)
        .flatMap((row) => (row.type === 'fold' && this.opened[this.foldKey(row)] ? row.lines : [row]));
    },

    pairs() {
      return diffTextSideBySide(this.rows);
    },

    stats() {
      return diffTextStats(this.rows);
    },

    split() {
      return this.mode === 'split';
    }
  },

  watch: {
    before() {
      this.opened = {};
    },

    after() {
      this.opened = {};
    }
  },

  methods: {
    t(key, parameters = {}) {
      return this.trans(`${DOMAIN}${key}`, parameters);
    },

    foldKey(fold) {
      return `${fold.old}:${fold.new}`;
    },

    open(fold) {
      this.opened = { ...this.opened, [this.foldKey(fold)]: true };
    },

    sign(type) {
      return type === 'add' ? '+' : type === 'remove' ? '−' : '';
    },

    cellClass(line) {
      return line ? `diff-text--cell--${line.type}` : 'diff-text--cell--empty';
    }
  }
};
</script>
