<script>
import FormField from '../../_abstract/form-field/form-field.vue';
import { otpInputCells, otpInputClean } from '../../../js/Helper/OtpInputHelper';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-otp-input-otp-input',
  emits: ['update:modelValue', 'complete'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    length: {
      type: Number,
      default: 6
    },
    alphanumeric: {
      type: Boolean,
      default: false
    },
    autoSubmit: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      caret: null
    };
  },

  computed: {
    cells() {
      return otpInputCells(this.modelValue || '', this.length, this.caret);
    },

    pattern() {
      return `${this.alphanumeric ? '[A-Za-z0-9]' : '[0-9]'}{${this.length}}`;
    }
  },

  mounted() {
    document.addEventListener('selectionchange', this.onSelectionChange);
  },

  beforeUnmount() {
    document.removeEventListener('selectionchange', this.onSelectionChange);
  },

  methods: {
    onInput(event) {
      const input = event.target;
      const wasComplete = (this.modelValue || '').length === this.length;
      const cleaned = otpInputClean(input.value, this.length, this.alphanumeric);

      if (cleaned !== input.value) {
        input.value = cleaned;
      }

      this.$emit('update:modelValue', cleaned);
      this.onSelectionChange();

      // Only on the keystroke that completes it, and never for an agent: the
      // submission stays with whoever asked for the code to be written.
      if (cleaned.length === this.length && !wasComplete && !this.isAssisted) {
        this.$emit('complete', cleaned);

        if (this.autoSubmit) {
          input.form?.requestSubmit();
        }
      }
    },

    onSelectionChange() {
      const input = this.$refs.input;

      this.caret = input && document.activeElement === input ? input.selectionStart : null;
    }
  }
};
</script>
