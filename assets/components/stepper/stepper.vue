<script>
import buildTranslatedBindings from '../../js/Helper/TranslationHelper';

const translated = buildTranslatedBindings({
  resolvedLabel: [
    'label',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.stepper.label'
  ]
});

// The twin of stepper.html.twig. Beside the links the twig draws, a step here
// can also be a button: a sequence held by the page — a form in several parts —
// moves on `select` rather than on a new address.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-stepper-stepper',

  props: {
    ...translated.props,
    // Each step is { label, href, unknown }: all optional, a step can be a
    // number alone. `unknown` stands for a step not decided yet — no number of
    // its own, and none of the numbers after it moved.
    steps: {
      type: Array,
      required: true
    },
    // Zero indexed: the steps before it are done, those after it ahead.
    current: {
      type: Number,
      default: 0
    },
    numbered: {
      type: Boolean,
      default: true
    },
    // Steps without an href answer a click with `select` instead of staying inert.
    navigable: {
      type: Boolean,
      default: false
    }
  },

  emits: ['select'],

  computed: {
    ...translated.computed,

    // What each step is called by: its rank among the known ones, or none.
    numbers() {
      let count = 0;

      return this.steps.map((step) => (step.unknown ? null : ++count));
    }
  },

  methods: {
    isLink(step, index) {
      return Boolean(step.href) && !step.unknown && index !== this.current;
    },

    stateOf(index) {
      if (index < this.current) {
        return 'done';
      }

      return index === this.current ? 'current' : 'upcoming';
    }
  }
};
</script>
