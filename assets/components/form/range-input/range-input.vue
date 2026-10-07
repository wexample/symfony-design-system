<script>
import FormField from '../../_abstract/form-field/form-field.vue';
import { numberFormat, numberStepDigits } from '../../../js/Helper/NumberHelper';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-range-input-range-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Number,
      default: null
    },
    min: {
      type: Number,
      default: 0
    },
    max: {
      type: Number,
      default: 100
    },
    step: {
      type: Number,
      default: 1
    },
    // A word after the value shown — a unit —, never part of what is emitted.
    suffix: {
      type: String,
      default: ''
    }
  },

  computed: {
    currentValue() {
      return this.modelValue === null || this.modelValue === undefined ? this.min : this.modelValue;
    },

    // The value as it is read, in the page's locale and with the decimals its
    // step asks for.
    displayValue() {
      return numberFormat(this.currentValue, numberStepDigits(this.step));
    },

    valueText() {
      return this.suffix ? `${this.displayValue} ${this.suffix}` : this.displayValue;
    },

    frozenText() {
      return this.valueText;
    }
  },

  methods: {
    onInput(event) {
      this.$emit('update:modelValue', Number(event.target.value));
    }
  }
};
</script>
