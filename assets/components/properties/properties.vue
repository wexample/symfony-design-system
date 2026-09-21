<script>

// The twin of properties.html.twig. The modifiers the twig side builds in
// PropertiesExtension are props here, listed once in MODIFIERS so the two
// sides can be read against each other.
const MODIFIERS = ['bordered', 'split', 'compact', 'stacked'];

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-properties-properties',

  props: {
    // Each item is { key, value, html }: html says the value is markup and is
    // rendered as given, exactly as the `|raw` of the twig does.
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
    extraClass: {
      type: String,
      default: null
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
