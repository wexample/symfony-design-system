<script>
import FormField from '../../_abstract/form-field/form-field.vue';
import { floatingFollow } from '../../../js/Helper/FloatingHelper';
import {
  OPTIONS_SOURCE_DELAY_MS,
  optionsFetch,
  optionsFilter,
  optionsMerge,
  optionsNormalize
} from '../../../js/Helper/OptionsHelper';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.search_select.';

let uid = 0;

// The twin of search-select-input.html.twig: a choice among options searched
// rather than all given — the field's own (`options`) and those a source
// answers for what is typed (`optionsUrl`, `?q=`), read as the tags field
// reads its suggestions (OptionsHelper). The value is a text, posted as it
// is; the option chosen is shown by its label, given with the value
// (`valueLabel`), found among the options, or asked of the source. Not
// required, the list opens on « — », taking the choice back.
export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-search-select-input-search-select-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    },
    // Words, or `{ value, label }`.
    options: {
      type: Array,
      default: () => []
    },
    // Asked for what is typed (`?q=`), answering words or `{ value, label }`.
    optionsUrl: {
      type: String,
      default: null
    },
    // The label of the value given, when no option given holds it.
    valueLabel: {
      type: String,
      default: null
    },
    placeholder: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      open: false,
      query: '',
      active: -1,
      fetched: [],
      loading: false,
      fetchTimer: null,
      fetchController: null,
      // The labels met so far, by value: the option chosen keeps its label
      // when a later search no longer lists it.
      labels: {},
      listId: `search-select-${++uid}`
    };
  },

  computed: {
    value() {
      return this.modelValue === null || this.modelValue === undefined ? '' : String(this.modelValue);
    },

    known() {
      return optionsMerge(optionsNormalize(this.options), this.fetched);
    },

    // The option chosen, by its label: not `label`, the field's own.
    chosenLabel() {
      if (!this.value) {
        return '';
      }

      return this.labels[this.value] ?? this.value;
    },

    // What the list shows: « — » first where the choice may be taken back.
    rows() {
      const offered = optionsFilter(this.known, this.query);
      const none = !this.required && !this.query ? [{ value: '', label: this.t('none'), none: true }] : [];

      return [...none, ...offered];
    },

    editable() {
      return !this.disabled && !this.readonly;
    },

    status() {
      if (this.loading && !this.rows.some((row) => !row.none)) {
        return this.t('loading');
      }

      return this.rows.some((row) => !row.none) ? '' : this.t('empty');
    }
  },

  watch: {
    query() {
      this.active = this.rows.length ? 0 : -1;
      this.askSource();
    },

    // An option is always pointed at while there is one: Enter takes it.
    rows(rows) {
      this.active = rows.length ? Math.min(Math.max(this.active, 0), rows.length - 1) : -1;
    },

    known: {
      handler(known) {
        this.learn(known);
      },
      immediate: true
    },

    value() {
      this.askValueLabel();
    }
  },

  created() {
    // Not reactive: nothing drawn reads it.
    this.labelsAsked = new Set();

    if (this.value && this.valueLabel) {
      this.labels = { ...this.labels, [this.value]: this.valueLabel };
    }
  },

  mounted() {
    document.addEventListener('pointerdown', this.onDocumentPointerdown);
    this.askValueLabel();
  },

  beforeUnmount() {
    document.removeEventListener('pointerdown', this.onDocumentPointerdown);
    this.unfollow?.();
    clearTimeout(this.fetchTimer);
    this.fetchController?.abort();
  },

  methods: {
    t(key) {
      return this.trans(`${DOMAIN}${key}`);
    },

    // Where the options are asked: the field's, or — for a field standardised
    // on this one — the one its defaults name (a route of the application).
    getOptionsUrl() {
      return this.optionsUrl;
    },

    openList() {
      if (!this.editable || this.open) {
        return;
      }

      this.open = true;
      this.query = '';
      this.active = Math.max(0, this.rows.findIndex((row) => row.value === this.value));
      this.askSource();
      this.$nextTick(() => {
        // In the top layer, out of what clips the field — a block, a zone's
        // body — as the server field's list (FloatingHelper).
        this.unfollow = floatingFollow(this.$refs.trigger, this.$refs.dropdown, { placement: 'bottom', align: 'start', offset: 4 });
        this.$refs.search?.focus();
      });
    },

    closeList(focusTrigger = false) {
      this.unfollow?.();
      this.unfollow = null;
      this.open = false;
      this.query = '';

      if (focusTrigger) {
        this.$nextTick(() => this.$refs.trigger?.focus());
      }
    },

    // Keeps the labels of the options met.
    learn(options) {
      if (options.length) {
        this.labels = { ...this.labels, ...Object.fromEntries(options.map((option) => [option.value, option.label])) };
      }
    },

    choose(row) {
      this.learn(row.none ? [] : [row]);
      this.$emit('update:modelValue', row.value);
      this.closeList(true);
    },

    onTriggerKeydown(event) {
      if (['ArrowDown', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        this.openList();
      }
    },

    onSearchKeydown(event) {
      const count = this.rows.length;

      if (event.key === 'ArrowDown' && count) {
        event.preventDefault();
        this.active = (this.active + 1) % count;
      } else if (event.key === 'ArrowUp' && count) {
        event.preventDefault();
        this.active = this.active <= 0 ? count - 1 : this.active - 1;
      } else if (event.key === 'Enter') {
        // Enter takes the option pointed at; it does not send the form.
        event.preventDefault();

        if (this.rows[this.active]) {
          this.choose(this.rows[this.active]);
        }
      } else if (event.key === 'Escape') {
        event.preventDefault();
        this.closeList(true);
      } else if (event.key === 'Tab') {
        this.closeList();
      }
    },

    onDocumentPointerdown(event) {
      if (this.open && !this.$el.contains(event.target)) {
        this.closeList();
      }
    },

    // The source asked once the typing pauses, the question before let go.
    askSource() {
      const url = this.getOptionsUrl();

      if (!url) {
        return;
      }

      clearTimeout(this.fetchTimer);
      this.loading = true;
      this.fetchTimer = setTimeout(async () => {
        this.fetchController?.abort();
        this.fetchController = new AbortController();
        this.fetched = await optionsFetch(url, this.query, this.fetchController.signal);
        this.loading = false;
      }, OPTIONS_SOURCE_DELAY_MS);
    },

    // A value no option given holds and no label given says: the source
    // asked for it, its own option taken where it answers one.
    async askValueLabel() {
      const url = this.getOptionsUrl();
      const value = this.value;

      // Asked once per value: a source that answers nothing is not asked again.
      if (!value || !url || value in this.labels || this.labelsAsked.has(value)) {
        return;
      }

      this.labelsAsked.add(value);

      const option = (await optionsFetch(url, value)).find((candidate) => candidate.value === value);

      if (option) {
        this.learn([option]);
      }
    },

    optionId(index) {
      return `${this.listId}-${index}`;
    },

    // A choice is taken, not spelled out.
    async writeValueAssisted(value) {
      this.$emit('update:modelValue', String(value ?? ''));
    }
  }
};
</script>
