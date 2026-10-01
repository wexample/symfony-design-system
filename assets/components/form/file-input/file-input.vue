<script>
import FormField from '../../_abstract/form-field/form-field.vue';

export default {
  extends: FormField,
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
    },
    // What the field holds: the files chosen, or — once frozen — what the
    // model keeps, a stored name or a path.
    modelValue: {
      type: [Array, String],
      default: null
    }
  },

  computed: {
    frozenText() {
      if (Array.isArray(this.modelValue)) {
        return this.modelValue.map((file) => file?.name ?? String(file)).join(', ');
      }

      return this.modelValue ?? '';
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
