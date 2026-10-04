<script>
import { assistanceWriteText } from '@wexample/js-api/Helper/Assistance';
import FormField from '../../_abstract/form-field/form-field.vue';
import {
  otpInputCells,
  otpInputClean,
  otpInputComplete,
  otpInputErase,
  otpInputFrom,
  otpInputMove,
  otpInputValue,
  otpInputWrite
} from '../../../js/Helper/OtpInputHelper';

// The same cells as the server-rendered field (otp-input.ts): a click lands on
// the cell clicked, a key replaces what it held, an erased digit leaves its
// cell empty; a paste or the phone's code is left to the browser.
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
      state: otpInputFrom(this.modelValue || '', this.length),
      focused: false
    };
  },

  watch: {
    modelValue(value) {
      if ((value || '') !== this.value) {
        this.state = otpInputFrom(value || '', this.length);
      }
    }
  },

  computed: {
    value() {
      return otpInputValue(this.state);
    },

    frozenText() {
      return this.value;
    },

    cells() {
      return otpInputCells(this.state, this.focused);
    },

    pattern() {
      return `${this.alphanumeric ? '[A-Za-z0-9]' : '[0-9]'}{${this.length}}`;
    }
  },

  methods: {
    onBeforeInput(event) {
      if (event.inputType === 'insertText' && event.data !== null) {
        event.preventDefault();
        const text = otpInputClean(event.data, this.length, this.alphanumeric);

        this.commit(text.length === this.length ? otpInputFrom(text, this.length) : otpInputWrite(this.state, text));
      } else if (event.inputType === 'deleteContentBackward' || event.inputType === 'deleteContentForward') {
        event.preventDefault();
        this.commit(otpInputErase(this.state, event.inputType === 'deleteContentBackward'));
      }
    },

    // What the browser wrote by itself replaces the code.
    onInput(event) {
      if (event.target.value !== this.value) {
        this.commit(otpInputFrom(otpInputClean(event.target.value, this.length, this.alphanumeric), this.length));
      }
    },

    onKeyDown(event) {
      const moves = {
        ArrowLeft: this.state.active - 1,
        ArrowRight: this.state.active + 1,
        Home: 0,
        End: this.length - 1
      };

      if (event.key in moves) {
        event.preventDefault();
        this.state = otpInputMove(this.state, moves[event.key]);
      }
    },

    onMouseDown(event) {
      const cells = Array.from(this.$el.querySelectorAll('.otp-input--cell'));
      const index = cells.findIndex((cell) => event.clientX < cell.getBoundingClientRect().right);

      event.preventDefault();
      this.state = otpInputMove(this.state, index === -1 ? this.length - 1 : index);
      this.$refs.input?.focus();
      this.selectAll();
    },

    onFocus() {
      this.focused = true;
      this.selectAll();
    },

    onBlur() {
      this.focused = false;
    },

    // All of it selected: a paste or the phone's code replaces the code.
    selectAll() {
      this.$nextTick(() => {
        const input = this.$refs.input;

        if (input && document.activeElement === input) {
          input.setSelectionRange(0, input.value.length);
        }
      });
    },

    commit(state) {
      const wasComplete = otpInputComplete(this.state);
      const input = this.$refs.input;

      this.state = state;

      if (input) {
        input.value = this.value;
      }

      this.$emit('update:modelValue', this.value);
      this.selectAll();

      // Only on what completes it, and never for an agent: the submission
      // stays with whoever asked for the code to be written.
      if (otpInputComplete(state) && !wasComplete && !this.isAssisted) {
        this.$emit('complete', this.value);

        if (this.autoSubmit && input?.form) {
          // Through the form's own button, which then shows it on its way.
          const button = Array.from(input.form.elements).find((element) => element.type === 'submit');
          input.form.requestSubmit(button);
        }
      }
    },

    // Spelled into the cells as a person would type it, through the same value.
    async writeValueAssisted(value, options) {
      await assistanceWriteText(
        (written) => {
          this.state = otpInputFrom(otpInputClean(written, this.length, this.alphanumeric), this.length);
          this.$emit('update:modelValue', this.value);
        },
        String(value ?? ''),
        options
      );
    }
  }
};
</script>
