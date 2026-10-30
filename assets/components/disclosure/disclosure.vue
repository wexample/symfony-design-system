<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';

// The twin of disclosure.html.twig. What it hides is mounted only once open:
// a content asked of the server on `open` is asked when someone wants it.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-disclosure-disclosure',

  emits: ['update:open', 'open'],

  props: {
    label: {
      type: String,
      default: ''
    },
    // How many things it hides, beside its label (`count`).
    count: {
      type: [Number, String],
      default: null
    },
    icon: {
      type: String,
      default: null
    },
    // Open at first; `v-model:open` to hold it from outside.
    open: {
      type: Boolean,
      default: false
    },
    // Small and muted, for the foot of a block.
    quiet: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      isOpen: this.open
    };
  },

  computed: {
    caretHtml() {
      return this.app.getServiceOrFail(IconService).icon('ph:bold/caret-right');
    },

    iconHtml() {
      return this.icon ? this.app.getServiceOrFail(IconService).icon(this.icon) : null;
    },

    hasCount() {
      return this.count !== null && this.count !== undefined && this.count !== '';
    }
  },

  watch: {
    open(open) {
      this.isOpen = open;
    }
  },

  methods: {
    // The browser opened or closed it — a click, Enter, Space —: the state
    // follows, and is said.
    onToggle(event) {
      const open = event.target.open;

      if (open === this.isOpen) {
        return;
      }

      this.isOpen = open;
      this.$emit('update:open', open);

      if (open) {
        this.$emit('open');
      }
    }
  }
};
</script>
