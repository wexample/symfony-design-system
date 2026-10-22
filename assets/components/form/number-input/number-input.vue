<script>
import TextInput from '../text-input/text-input.vue';

const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.number_stepper.';

// A number field; `stepper` puts a button on each side to take one step down
// or up — the field's `step`, one where it has none —, held within its `min`
// and `max`: a quantity.
export default {
  extends: TextInput,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-number-input-number-input',

  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    },
    type: { default: 'number' },
    min: {
      type: Number,
      default: null
    },
    max: {
      type: Number,
      default: null
    },
    step: {
      type: [Number, String],
      default: null
    },
    stepper: {
      type: Boolean,
      default: false
    }
  },

  // The value held here too: a step shows even where no parent binds it.
  data() {
    return {
      current: this.modelValue
    };
  },

  watch: {
    modelValue(value) {
      this.current = value;
    }
  },

  computed: {
    inputAttrs() {
      return { value: this.current, min: this.min, max: this.max, step: this.step };
    },

    numberValue() {
      const value = Number.parseFloat(this.current);

      return Number.isNaN(value) ? null : value;
    },

    // A bound reached, its button rests.
    canStepDown() {
      return !this.disabled && !this.readonly && (this.min === null || this.numberValue === null || this.numberValue > this.min);
    },

    canStepUp() {
      return !this.disabled && !this.readonly && (this.max === null || this.numberValue === null || this.numberValue < this.max);
    }
  },

  methods: {
    t(key) {
      return this.trans(`${T}${key}`);
    },

    icon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    },

    stepBy(direction) {
      const step = Number(this.step) > 0 ? Number(this.step) : 1;
      let value = (this.numberValue ?? 0) + direction * step;

      if (this.min !== null) {
        value = Math.max(value, this.min);
      }

      if (this.max !== null) {
        value = Math.min(value, this.max);
      }

      // No float dust: 0.1 + 0.2 is written 0.3.
      this.current = String(Number(value.toFixed(10)));
      this.$emit('update:modelValue', this.current);
    },

    onInput(event) {
      this.current = event?.target?.value ?? '';
      this.$emit('update:modelValue', this.current);
    }
  }
};
</script>
