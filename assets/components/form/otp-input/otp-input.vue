<script>
import { assistanceWriteText } from '@wexample/js-api/Helper/Assistance';
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
      // Held here and not only read from the prop: dropped in a page without a
      // v-model, the value it emits comes back to nobody, and the cells would
      // stay empty under a field being written in.
      value: this.modelValue || '',
      caret: null
    };
  },

  watch: {
    modelValue(value) {
      this.value = value || '';
    }
  },

  computed: {
    cells() {
      return otpInputCells(this.value, this.length, this.caret);
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
      const wasComplete = this.value.length === this.length;
      const cleaned = otpInputClean(input.value, this.length, this.alphanumeric);

      if (cleaned !== input.value) {
        input.value = cleaned;
      }

      this.value = cleaned;
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

    // Spelled into the cells as a person would type it, through the same value.
    async writeValueAssisted(value, options) {
      await assistanceWriteText(
        (written) => {
          this.value = otpInputClean(written, this.length, this.alphanumeric);
          this.$emit('update:modelValue', this.value);
        },
        String(value ?? ''),
        options
      );
    },

    onSelectionChange() {
      const input = this.$refs.input;

      this.caret = input && document.activeElement === input ? input.selectionStart : null;
    }
  }
};
</script>
