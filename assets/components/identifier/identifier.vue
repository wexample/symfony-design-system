<script>
import CopyButton from '../copy-button/copy-button.vue';

// The twin of identifier.html.twig: an id read as code, kept to its end when
// `short`, copied whole with a toast when `copy`.
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
    }
  },

  computed: {
    full() {
      return this.value === null || this.value === undefined ? '' : String(this.value);
    },

    shown() {
      const length = this.short === true ? 8 : Number(this.short) || 0;

      return length > 0 && this.full.length > length ? this.full.slice(-length) : this.full;
    }
  }
};
</script>
