<script>
import FormField from '../../_abstract/form-field/form-field.vue';

// Where the field's words are, shared with the rest of the system's frontend.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.tags_input.';

// The twin of tags-input.html.twig: the same frame, the same tags, holding a
// list rather than a text joined by a separator. Enter — or the separator —
// adds what is typed, Backspace on an empty entry takes the last tag back, a
// press on a tag's × removes it. The words offered (`suggestions`) not taken
// yet are one press away under the field.
export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-tags-input-tags-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Array,
      default: () => []
    },
    placeholder: {
      type: String,
      default: null
    },
    separator: {
      type: String,
      default: ','
    },
    suggestions: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      entry: ''
    };
  },

  computed: {
    entryPlaceholder() {
      return this.modelValue.length ? null : (this.placeholder ?? this.trans(`${DOMAIN}add`));
    },

    offered() {
      return this.suggestions.filter((tag) => !this.modelValue.includes(tag));
    },

    removeLabel() {
      return this.trans(`${DOMAIN}remove`);
    }
  },

  methods: {
    write(tags) {
      this.$emit('update:modelValue', [...new Set(tags.map((tag) => String(tag).trim()).filter(Boolean))]);
    },

    // What is typed becomes tags, the separator splitting it.
    commit() {
      if (!this.entry.trim()) {
        return;
      }

      this.write([...this.modelValue, ...this.entry.split(this.separator)]);
      this.entry = '';
    },

    remove(tag) {
      this.write(this.modelValue.filter((candidate) => candidate !== tag));
      this.$refs.entry?.focus();
    },

    onKeydown(event) {
      if (event.key === 'Enter' || event.key === this.separator) {
        // Enter adds a tag, it does not send the form.
        event.preventDefault();
        this.commit();
      } else if (event.key === 'Backspace' && this.entry === '' && this.modelValue.length) {
        this.write(this.modelValue.slice(0, -1));
      }
    },

    suggestLabel(tag) {
      return this.trans(`${DOMAIN}suggest`, { '%tag%': tag });
    }
  }
};
</script>
