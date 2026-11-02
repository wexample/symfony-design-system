<script>
import FormField from '../../_abstract/form-field/form-field.vue';

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
    }
  },

  computed: {
    resolvedValue() {
      const current = this.modelValue === undefined || this.modelValue === null
        ? ''
        : String(this.modelValue);
      const hasCurrent = this.options.some((option) => this.resolveOptionValue(option) === current);

      if (hasCurrent) {
        return current;
      }

      if (!this.options.length) {
        return current;
      }

      return this.resolveOptionValue(this.options[0]);
    },

    frozenText() {
      const option = this.options.find((entry) => this.resolveOptionValue(entry) === this.resolvedValue);

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
        const choice = this.options.find((candidate) => this.resolveOptionValue(candidate).toLowerCase() === wanted
          || String(this.resolveOptionLabel(candidate)).toLowerCase() === wanted);

        if (!choice) {
          throw new Error(`No choice "${option ?? value}": ${this.options.map((candidate) => this.resolveOptionLabel(candidate)).join(', ')}.`);
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
