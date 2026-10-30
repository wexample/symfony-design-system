<script>

import Spinner from '../spinner/spinner.vue';
import { rippleAgentPress } from '../../js/Helper/RippleHelper';

// The twin of button.html.twig: the same three spans, the same classes. What the
// twig reads off its options, this reads off props; the click it cannot handle
// itself is emitted, since inside a vue tree the parent is what knows.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-button-button',

  components: {
    Spinner
  },

  emits: ['click'],

  props: {
    label: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: null
    },
    type: {
      type: String,
      default: 'button'
    },
    disabled: {
      type: Boolean,
      default: false
    },
    isLoading: {
      type: Boolean,
      default: false
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  },

  methods: {
    // What an agent reads and does (`button.ai.yml`, shared with the twig
    // twin): pressed as the reader presses it, the parent hearing the click.
    getAgentLabel() {
      return this.label || undefined;
    },

    getAgentState() {
      return { disabled: this.disabled || this.isLoading, submits: this.type === 'submit', confirms: false };
    },

    getAgentActions() {
      return {
        click: async () => {
          if (this.disabled || this.isLoading) {
            throw new Error(`"${this.label}" is disabled.`);
          }

          await rippleAgentPress(this.app, this.$el);
          this.$el.click();

          return { clicked: this.label };
        }
      };
    }
  }
};
</script>
