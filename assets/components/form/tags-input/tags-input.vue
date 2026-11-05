<script>
import FormField from '../../_abstract/form-field/form-field.vue';
import {
  TAGS_SUGGESTIONS_DELAY_MS,
  tagsFetchSuggestions,
  tagsFilterSuggestions,
  tagsJoin,
  tagsMergeSuggestions,
  tagsNormalizeSuggestions,
  tagsSplit,
  tagsUnique
} from '../../../js/Helper/TagsHelper';

// Where the field's words are, shared with the rest of the system's frontend.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.tags_input.';

let uid = 0;

// The twin of tags-input.html.twig: the same frame, the same tags — markers,
// one tone and one glyph for the field —, the same words offered. Enter — or
// the separator — adds what is typed, or the word offered that is pointed
// at; Backspace on an empty entry takes the last tag back, a tag's × takes it
// off. The words offered are the field's own (`suggestions`) and those a
// source answers for what is typed (`suggestionsUrl`), the ones taken left
// out. What is held is a list; posted by a form, the tags go joined by the
// separator, as the server field posts them.
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
    // Words, or `{ value, label }`.
    suggestions: {
      type: Array,
      default: () => []
    },
    // Asked for what is typed (`?q=`), answering words or `{ value, label }`.
    suggestionsUrl: {
      type: String,
      default: null
    },
    // The marker every tag of the field wears: a state's tone or a category's
    // (`cat-straw`), and a glyph before its word.
    tone: {
      type: String,
      default: 'neutral'
    },
    glyph: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      entry: '',
      focused: false,
      active: -1,
      fetched: [],
      fetchTimer: null,
      fetchController: null,
      menuId: `tags-input-menu-${++uid}`
    };
  },

  computed: {
    tags() {
      return tagsUnique(this.modelValue ?? []);
    },

    joined() {
      return tagsJoin(this.tags, this.separator);
    },

    editable() {
      return !this.disabled && !this.readonly;
    },

    entryPlaceholder() {
      return this.tags.length ? null : (this.placeholder ?? this.trans(`${DOMAIN}add`));
    },

    offered() {
      return tagsFilterSuggestions(
        tagsMergeSuggestions(tagsNormalizeSuggestions(this.suggestions), this.fetched),
        this.tags,
        this.entry
      );
    },

    menuOpen() {
      return this.editable && this.focused && this.offered.length > 0;
    },

    glyphHtml() {
      return this.glyph ? this.app.getServiceOrFail('icon').icon(this.glyph) : '';
    },

    removeLabel() {
      return this.trans(`${DOMAIN}remove`);
    }
  },

  watch: {
    entry() {
      this.active = -1;
      this.askSource();
    },

    offered(offered) {
      this.active = Math.min(this.active, offered.length - 1);
    }
  },

  beforeUnmount() {
    clearTimeout(this.fetchTimer);
    this.fetchController?.abort();
  },

  methods: {
    // Where the words are asked: the field's, or — for a field standardised
    // on this one — the one its defaults name (a route of the application).
    getSuggestionsUrl() {
      return this.suggestionsUrl;
    },

    write(tags) {
      this.$emit('update:modelValue', tagsUnique(tags));
    },

    // What is typed becomes tags, the separator splitting it.
    commit() {
      if (!this.entry.trim()) {
        return;
      }

      this.write([...this.tags, ...tagsSplit(this.entry, this.separator)]);
      this.entry = '';
    },

    take(suggestion) {
      this.write([...this.tags, suggestion.value]);
      this.entry = '';
      this.$refs.entry?.focus();
    },

    remove(tag) {
      this.write(this.tags.filter((candidate) => candidate !== tag));
      this.$refs.entry?.focus();
    },

    onFocus() {
      this.focused = true;
      this.askSource();
    },

    onBlur() {
      this.focused = false;
      this.commit();
    },

    onKeydown(event) {
      if (event.key === 'ArrowDown' && this.offered.length) {
        event.preventDefault();
        this.active = (this.active + 1) % this.offered.length;
      } else if (event.key === 'ArrowUp' && this.offered.length) {
        event.preventDefault();
        this.active = this.active <= 0 ? this.offered.length - 1 : this.active - 1;
      } else if (event.key === 'Escape' && this.menuOpen) {
        event.preventDefault();
        this.focused = false;
      } else if (event.key === 'Enter' || event.key === this.separator) {
        // Enter adds a tag, it does not send the form.
        event.preventDefault();

        if (this.menuOpen && this.active >= 0) {
          this.take(this.offered[this.active]);
        } else {
          this.commit();
        }
      } else if (event.key === 'Backspace' && this.entry === '' && this.tags.length) {
        this.write(this.tags.slice(0, -1));
      }
    },

    // The source asked once the typing pauses, the question before let go.
    askSource() {
      const url = this.getSuggestionsUrl();

      if (!url || !this.editable) {
        return;
      }

      clearTimeout(this.fetchTimer);
      this.fetchTimer = setTimeout(async () => {
        this.fetchController?.abort();
        this.fetchController = new AbortController();
        this.fetched = await tagsFetchSuggestions(url, this.entry, this.fetchController.signal);
      }, TAGS_SUGGESTIONS_DELAY_MS);
    },

    optionId(index) {
      return `${this.menuId}-${index}`;
    },

    suggestLabel(suggestion) {
      return this.trans(`${DOMAIN}suggest`, { '%tag%': suggestion.label });
    }
  }
};
</script>
