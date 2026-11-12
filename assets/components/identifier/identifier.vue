<script>
import CopyButton from '../copy-button/copy-button.vue';

// The twin of identifier.html.twig: an id read as code, kept to its end when
// `short`, copied whole with a toast when `copy`. An id leading to its record
// (`link`) is pressed as a link and says so (`open`, its whole value, then the click: a key held reads there): every
// id that leads somewhere reads and acts the same, wherever it is drawn —
// without its copy then, a button holding no other.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-identifier-identifier',

  components: {
    CopyButton
  },

  props: {
    value: {
      type: [String, Number],
      default: ''
    },
    // True for eight characters, or how many to keep from the end.
    short: {
      type: [Boolean, Number],
      default: false
    },
    copy: {
      type: Boolean,
      default: false
    },
    link: {
      type: Boolean,
      default: false
    },
    // What pressing it opens, said under the pointer before the id itself.
    tooltip: {
      type: String,
      default: null
    }
  },

  emits: ['open'],

  computed: {
    full() {
      return this.value === null || this.value === undefined ? '' : String(this.value);
    },

    // Under the pointer: what it opens, then the whole id when it is cut.
    hint() {
      const parts = [this.tooltip, this.shown !== this.full ? this.full : null].filter(Boolean);

      return parts.length ? parts.join(' · ') : null;
    },

    shown() {
      const length = this.short === true ? 8 : Number(this.short) || 0;

      return length > 0 && this.full.length > length ? this.full.slice(-length) : this.full;
    }
  }
};
</script>
