<script>

// The twin of properties.html.twig. The modifiers the twig side builds in
// PropertiesExtension are props here, listed once in MODIFIERS so the two
// sides can be read against each other.
const MODIFIERS = ['bordered', 'split', 'compact', 'stacked', 'columns'];

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-properties-properties',

  props: {
    // Each item is { key, value, html, full }: html says the value is markup
    // and is rendered as given, exactly as the `|raw` of the twig does, and
    // full gives a value the whole width where the list is laid in columns.
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
