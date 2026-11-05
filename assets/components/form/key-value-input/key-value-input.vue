<script>
import FormField from '../../_abstract/form-field/form-field.vue';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.key_value.';

// Pairs of words, a key and its value, one line each — metadata, headers,
// parameters —, held as a list `[{ key, value }]` in the order written, a
// key left empty kept while it is typed. The last line is the next pair:
// what is typed in it, key or value, opens it, and the line stays the one
// written in. A line's × takes its pair off; a key written twice is said on
// the key. A record `{ lang: 'fr' }` given as the value is read as its pairs.
export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-key-value-input-key-value-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: [Array, Object],
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

  computed: {
    pairs() {
      if (Array.isArray(this.modelValue)) {
        return this.modelValue;
      }

      return Object.entries(this.modelValue ?? {}).map(([key, value]) => ({ key, value: String(value ?? '') }));
    },

    // Each pair a line, and the next pair last, written in place.
    lines() {
      return [...this.pairs.map((pair) => ({ ...pair, isNew: false })), { key: '', value: '', isNew: true }];
    },

    editable() {
      return !this.disabled && !this.readonly;
    },

    // The keys written more than once, trimmed as they are read back.
    duplicates() {
      const seen = new Set();
      const twice = new Set();

      this.pairs.forEach(({ key }) => {
        const trimmed = String(key ?? '').trim();

        if (trimmed && seen.has(trimmed)) {
          twice.add(trimmed);
        }

        seen.add(trimmed);
      });

      return twice;
    }
  },

  methods: {
    t(key) {
      return this.trans(`${DOMAIN}${key}`);
    },

    isDuplicate(line) {
      return !line.isNew && this.duplicates.has(String(line.key ?? '').trim());
    },

    // Written in a line: a pair changed, or — the last line — opened.
    update(index, field, value) {
      const pairs = this.pairs.map((pair) => ({ ...pair }));

      if (index >= pairs.length) {
        if (value === '') {
          return;
        }

        pairs.push({ key: '', value: '', [field]: value });
      } else {
        pairs[index][field] = value;
      }

      this.$emit('update:modelValue', pairs);
    },

    remove(index) {
      this.$emit('update:modelValue', this.pairs.filter((pair, at) => at !== index));
      this.$nextTick(() => this.focusLine(Math.min(index, this.pairs.length), 'key'));
    },

    // Enter goes on to the next line's key, as a sheet does.
    next(index) {
      this.focusLine(index + 1, 'key');
    },

    // Emptied, a line's Backspace takes it off and goes back up.
    onKeyBackspace(index, event) {
      const line = this.lines[index];

      if (line.isNew || line.key !== '' || line.value !== '') {
        return;
      }

      event.preventDefault();
      this.remove(index);
      this.$nextTick(() => this.focusLine(Math.max(0, index - 1), 'value'));
    },

    focusLine(index, field) {
      this.$el.querySelector(`[data-pair="${index}"] .key-value-input--${field}`)?.focus();
    }
  }
};
</script>
