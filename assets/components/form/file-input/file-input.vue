<script>
import BaseField from '../../bases/form-field/form-field.vue';

export default {
  extends: BaseField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-file-input-file-input',
  emits: ['update:modelValue'],

  props: {
    multiple: {
      type: Boolean,
      default: false
    },
    accept: {
      type: String,
      default: ''
    }
  },

  methods: {
    onChange(event) {
      const files = event?.target?.files;
      this.$emit('update:modelValue', files ? Array.from(files) : []);
    }
  }
};
</script>
