<script>
import FormField from '../../_abstract/form-field/form-field.vue';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.key_value.';

// Pairs of words, a key and its value, one line each — metadata, headers,
// parameters —, held as a list `[{ key, value }]`, kept in the order written
// and through a key left empty while it is typed. A key typed in the last line
// opens a pair; a line's × takes it off.
export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-key-value-input-key-value-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Array,
      default: () => []
    },
    keyPlaceholder: {
      type: String,
      default: null
    },
    valuePlaceholder: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      draft: ''
    };
  },

  methods: {
    t(key) {
      return this.trans(`${DOMAIN}${key}`);
    },

    update(index, field, value) {
      this.$emit('update:modelValue', this.modelValue.map((pair, at) => (at === index ? { ...pair, [field]: value } : pair)));
    },

    remove(index) {
      this.$emit('update:modelValue', this.modelValue.filter((pair, at) => at !== index));
    },

    // A key typed in the last line: a pair opened, its value next.
    add() {
      const key = this.draft.trim();

      if (!key) {
        return;
      }

      this.$emit('update:modelValue', [...this.modelValue, { key, value: '' }]);
      this.draft = '';
      this.$nextTick(() => this.$el.querySelector(`[data-pair="${this.modelValue.length - 1}"] .key-value-input--value`)?.focus());
    }
  }
};
</script>
