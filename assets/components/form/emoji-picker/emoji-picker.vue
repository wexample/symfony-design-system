<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-emoji-picker-emoji-picker',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    }
  },

  computed: {
    frozenText() {
      return this.modelValue ?? '';
    }
  },

  methods: {
    onInput(event) {
      this.$emit('update:modelValue', event?.target?.value ?? '');
    }
  }
};
</script>
