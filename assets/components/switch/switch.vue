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

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    }
  },

  methods: {
    toggle() {
      const next = !this.isChecked;

      this.innerChecked = next;
      this.$emit('update:modelValue', next);
      this.$emit('change', next);
    }
  }
};
</script>
