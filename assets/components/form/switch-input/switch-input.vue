<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-switch-input-switch-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    // What a frozen switch says of its state: a switch is seldom "on" and
    // "off", so the page names the two — "Active", "Deactivated". Left empty,
    // "On" and "Off".
    checkedLabel: {
      type: String,
      default: ''
    },
    uncheckedLabel: {
      type: String,
      default: ''
    }
  },

  computed: {
    frozenText() {
      const label = this.modelValue ? this.checkedLabel : this.uncheckedLabel;

      return label
        ? this.resolveLabel(label)
        : this.trans(`WexampleSymfonyDesignSystemBundle.common.system::frontend.switch.${this.modelValue ? 'checked' : 'unchecked'}`);
    }
  },

  methods: {
    onChange(event) {
      this.$emit('update:modelValue', event.target.checked);
    }
  }
};
</script>
