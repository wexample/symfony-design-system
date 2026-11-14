<script>
import FormField from '../../_abstract/form-field/form-field.vue';
import { optionsFetch } from '../../../js/Helper/OptionsHelper';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-select-input-select-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    options: {
      type: Array,
      default: () => []
    },
    // Where the choices are read again as a field they follow changes — that
    // field's value already in it (dynamic-field) —, answering words or
    // `{ value, label }`. None, the choices are the ones given.
    optionsUrl: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      // The choices the source answered, in place of the ones given.
      sourced: null
    };
  },

  watch: {
    // What the field follows changed: its choices read again, and the value
    // held left for the first of them when they no longer hold it.
    optionsUrl: {
      immediate: true,
      async handler(url, before) {
        if (!url) {
          this.sourced = before ? [] : null;

          return;
        }

        const options = await optionsFetch(url, '');

        if (url !== this.optionsUrl) {
          return;
        }

        this.sourced = options;

        if (before !== undefined && !options.some((option) => option.value === String(this.modelValue ?? ''))) {
          this.$emit('update:modelValue', options[0]?.value ?? '');
        }
      }
    }
  },

  computed: {
    // The choices shown: the source's once it answered, the ones given till then.
    shownOptions() {
      return this.sourced ?? this.options;
    },

    resolvedValue() {
      const current = this.modelValue === undefined || this.modelValue === null
        ? ''
        : String(this.modelValue);
      const hasCurrent = this.shownOptions.some((option) => this.resolveOptionValue(option) === current);

      if (hasCurrent) {
        return current;
      }

      if (!this.shownOptions.length) {
        return current;
      }

      return this.resolveOptionValue(this.shownOptions[0]);
    },

    frozenText() {
      const option = this.shownOptions.find((entry) => this.resolveOptionValue(entry) === this.resolvedValue);

      return option ? this.resolveOptionLabel(option) : '';
    }
  },

  methods: {
    // A choice is taken, not spelled out.
    async writeValueAssisted(value) {
      this.$emit('update:modelValue', String(value ?? ''));
    },

    // `set` takes a choice's value or its label; `choose` is the same, said
    // as the contract says it — as the twig select does (select/select.ts).
    getAgentActions() {
      const choose = async ({ value, option }) => {
        const wanted = String(option ?? value ?? '').toLowerCase();
        const choice = this.shownOptions.find((candidate) => this.resolveOptionValue(candidate).toLowerCase() === wanted
          || String(this.resolveOptionLabel(candidate)).toLowerCase() === wanted);

        if (!choice) {
          throw new Error(`No choice "${option ?? value}": ${this.shownOptions.map((candidate) => this.resolveOptionLabel(candidate)).join(', ')}.`);
        }

        await this.setValueAssisted(this.resolveOptionValue(choice));

        return { value: this.resolveOptionValue(choice), label: this.resolveOptionLabel(choice) };
      };

      return {
        ...FormField.methods.getAgentActions.call(this),
        get: () => ({ value: this.resolvedValue, label: this.frozenText }),
        set: choose,
        choose
      };
    },

    onChange(event) {
      this.$emit('update:modelValue', event?.target?.value ?? '');
    },

    resolveOptionLabel(option) {
      const label = option?.label ?? '';
      return this.resolveLabel(label);
    },

    resolveOptionValue(option) {
      const value = option?.value;
      return value === undefined || value === null ? '' : String(value);
    }
  }
};
</script>
