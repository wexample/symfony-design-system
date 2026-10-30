<script>
import Identifier from '../identifier/identifier.vue';

// The twin of properties.html.twig. The modifiers the twig side builds in
// PropertiesExtension are props here, listed once in MODIFIERS so the two
// sides can be read against each other.
const MODIFIERS = ['bordered', 'split', 'compact', 'stacked', 'columns', 'tiles'];

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-properties-properties',

  components: {
    Identifier
  },

  props: {
    // Each item is { key, value, html, full, break, code, short, copy }: html
    // says the value is markup and is rendered as given, exactly as the `|raw`
    // of the twig does, full gives a value the whole width where the list is
    // laid in columns or tiles, break breaks it anywhere — a path, an address
    // —, and code reads it as an identifier (`identifier`), kept to its end
    // with short, copied with copy.
    items: {
      type: Array,
      required: true
    },
    bordered: {
      type: Boolean,
      default: false
    },
    split: {
      type: Boolean,
      default: false
    },
    compact: {
      type: Boolean,
      default: false
    },
    stacked: {
      type: Boolean,
      default: false
    },
    columns: {
      type: Boolean,
      default: false
    },
    // Each pair a tile of its own, as many side by side as the width holds.
    tiles: {
      type: Boolean,
      default: false
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  methods: {
    // A field nobody filled is said with a dash: a blank space reads as
    // something missing from the page, a dash as something missing from the
    // thing described, which is the truth.
    isEmpty(item) {
      return item.value === null || item.value === undefined || item.value === '';
    },

    valueClass(item) {
      return {
        'properties--value--break': item.break
      };
    },

    rowClass(item) {
      return ['properties--row', item.full ? 'properties--row--full' : null];
    }
  },

  computed: {
    listClass() {
      const classes = ['properties'];

      MODIFIERS.forEach((modifier) => {
        if (this[modifier]) {
          classes.push(`properties--${modifier}`);
        }
      });

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    }
  }
};
</script>
