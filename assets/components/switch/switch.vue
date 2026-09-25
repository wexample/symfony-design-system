<script>

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
    async toggle() {
      const next = !this.isChecked;

      if (next && this.confirm?.message && !(await this.askConfirmation())) {
        return;
      }

      this.innerChecked = next;
      this.$emit('update:modelValue', next);
      this.$emit('change', next);
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
