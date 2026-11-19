<script>
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.field_variable.';

// The `variable` extension of a field (FieldExtensionsHelper): the field
// bound to a variable by its name — a test matrix's, the field keeping its
// own value outside it —, a value of its own beside the field's, which it
// never touches. In the strip under the field: its glyph, lit while bound,
// the name beside it; pressed, the name written — the names in use offered
// —; emptied, unbound. The strip is the field's first, the one an
// expression's switch stands in, when there is one; its own else.
//
// Its value is sent under `<field>__variable` (FormDefinitionHelper): kept
// in the dynamic form's context, posted with the fields; on a page's field
// (`target`, the twig theme), in a hidden input of that name.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-extension-variable-variable',

  inject: {
    dynamicFormContext: { default: null }
  },

  emits: ['update:value'],

  props: {
    // The name the field is bound to; null, none.
    value: {
      type: String,
      default: null
    },
    pattern: {
      type: String,
      default: '^[A-Za-z][A-Za-z0-9_]{0,63}$'
    },
    icon: {
      type: String,
      default: 'ph:bold/brackets-curly'
    },
    label: {
      type: String,
      default: null
    },
    suggestions: {
      type: Array,
      default: () => []
    },
    // The field's name, as it is posted.
    fieldName: {
      type: String,
      default: null
    },
    // The page's field, by its id (the twig theme).
    target: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      bound: this.value || null,
      editing: false,
      draft: '',
      // The strip it stands in: the field's first, when another drew one.
      joined: null
    };
  },

  computed: {
    // `<field>__variable`, as the form reads it back.
    postedName() {
      const name = this.target ? document.getElementById(this.target)?.getAttribute('name') : this.fieldName;

      return name ? name.replace(/(\]?)$/, '__variable$1') : null;
    },

    // Its key in the dynamic form's context: the field's own name.
    contextKey() {
      const name = this.fieldName ?? '';
      const own = /\[([^\]]*)\]$/.exec(name)?.[1] ?? name;

      return own ? `${own}__variable` : null;
    },

    title() {
      return this.label || this.t('label');
    },

    valid() {
      return this.draft === '' || new RegExp(this.pattern).test(this.draft);
    },

    // Its glyph, as the page's icons are drawn.
    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    },

    listId() {
      return `field-variable-${this.$.uid}`;
    }
  },

  watch: {
    value(value) {
      this.bound = value || null;
    }
  },

  mounted() {
    this.join();
    // A page's fields are drawn one app each: the strip it joins may come
    // a moment later.
    if (!this.joined && this.target) {
      requestAnimationFrame(() => this.join());
    }
  },

  methods: {
    t(key, args) {
      return this.trans(`${DOMAIN}${key}`, args);
    },

    // The field's first strip, drawn by another of its extensions.
    join() {
      const group = this.$el?.closest?.('.form--group, .field-extended');
      const strip = group ? [...group.querySelectorAll('.field-extension--strip')].find((element) => !this.$el.contains(element)) : null;
      this.joined = strip ?? null;
    },

    edit() {
      this.draft = this.bound ?? '';
      this.editing = true;
      this.$nextTick(() => this.$refs.input?.focus());
    },

    // The name written: kept when it is one — emptied, unbound —, the
    // field told; a name it refuses keeps the writing open.
    commit() {
      if (!this.editing) {
        return;
      }

      if (!this.valid) {
        return;
      }

      this.editing = false;
      this.write(this.draft.trim() || null);
    },

    cancel() {
      this.editing = false;
    },

    write(name) {
      this.bound = name;

      if (this.dynamicFormContext?.extensionValues && this.contextKey) {
        this.dynamicFormContext.extensionValues[this.contextKey] = name;
      }

      this.$emit('update:value', name);
    }
  }
};
</script>
