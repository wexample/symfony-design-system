<script>
import { rippleAgentPress } from '../../js/Helper/RippleHelper';

// The twin of switch.html.twig. Server-side the toggling is switch.ts flipping
// a data attribute; here it is state, so the component is usable with v-model
// and still works uncontrolled when the parent binds nothing.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-switch-switch',

  emits: ['update:modelValue', 'change'],

  props: {
    modelValue: {
      type: Boolean,
      default: null
    },
    checked: {
      type: Boolean,
      default: false
    },
    label: {
      type: String,
      default: null
    },
    labelPosition: {
      type: String,
      default: 'right'
    },
    // Markup shown inside the pill on each side, as the twig `|raw` slots are.
    offInner: {
      type: String,
      default: null
    },
    onInner: {
      type: String,
      default: null
    },
    extraClass: {
      type: String,
      default: null
    },
    // The colour the pill takes once on: a state or a `cat-*` colour.
    tone: {
      type: String,
      default: null
    },
    // The name of a switch that shows no label, and its tooltip.
    ariaLabel: {
      type: String,
      default: null
    },
    // { title, message, accept }, asked before switching on.
    confirm: {
      type: Object,
      default: null
    }
  },

  data() {
    return {
      innerChecked: this.checked
    };
  },

  computed: {
    isChecked() {
      return this.modelValue === null ? this.innerChecked : this.modelValue;
    },

    wrapperClass() {
      const classes = ['switch'];

      if (this.labelPosition === 'left') {
        classes.push('switch--label-left');
      }

      if (this.tone) {
        classes.push(`switch--${this.tone}`);
      }

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    }
  },

  methods: {
    toggle() {
      return this.switchTo(!this.isChecked);
    },

    // Whether it switched: a switch asking before turning on stays off when
    // the reader refuses.
    async switchTo(next) {
      if (next === this.isChecked) {
        return true;
      }

      if (next && this.confirm?.message && !(await this.askConfirmation())) {
        return false;
      }

      this.innerChecked = next;
      this.$emit('update:modelValue', next);
      this.$emit('change', next);

      return true;
    },

    // What an agent reads and does (`switch.ai.yml`, shared with switch.ts):
    // the same as a click, confirmation included.
    getAgentLabel() {
      return this.label || this.ariaLabel || undefined;
    },

    getAgentState() {
      return { checked: this.isChecked };
    },

    getAgentActions() {
      // Read once the parent bound to v-model has taken the new value.
      const outcome = async (switched) => {
        await this.$nextTick();
        return { checked: this.isChecked, switched };
      };
      // Pressed where the reader sees it, then switched.
      const press = () => rippleAgentPress(this.app, this.$el.querySelector('.switch--pill'));

      return {
        get: () => ({ checked: this.isChecked }),
        set: async ({ checked }) => {
          await press();
          return outcome(await this.switchTo(checked === true));
        },
        toggle: async () => {
          await press();
          return outcome(await this.switchTo(!this.isChecked));
        }
      };
    },

    async askConfirmation() {
      const confirmService = this.app.services.confirm;

      if (!confirmService) {
        return window.confirm(this.confirm.message);
      }

      const result = await confirmService.confirm({
        title: this.confirm.title || undefined,
        message: this.confirm.message,
        preset: 'ok_cancel',
        actions: this.confirm.accept
          ? [
            { key: 'y', value: 'ok', label: this.confirm.accept, role: 'primary' },
            { key: 'n', value: 'cancel', label: this.trans('WexampleSymfonyDesignSystemBundle.common.system::frontend.switch.cancel'), role: 'secondary' }
          ]
          : undefined
      });

      return result === 'ok';
    }
  }
};
</script>
