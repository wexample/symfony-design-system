<script>
// The `toggle` extension of a field (FieldExtensionsHelper): whether the
// field is something more — entered in a test matrix —, a yes or no of its
// own beside the field's value, which it never touches. Its glyph alone in
// the strip under the field — the field's first, the one an expression's
// switch stands in, when there is one; its own else —, lit while on, its
// label under the pointer, pressed to switch.
//
// Its value is sent under `<field>__toggle` (FormDefinitionHelper): kept in
// the dynamic form's context, posted with the fields; on a page's field
// (`target`, the twig theme), in a hidden input of that name, « 1 » or « 0 ».
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-extension-toggle-toggle',

  inject: {
    dynamicFormContext: { default: null }
  },

  emits: ['update:value'],

  props: {
    value: {
      type: Boolean,
      default: false
    },
    icon: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
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
      on: this.value,
      // The strip it stands in: the field's first, when another drew one.
      joined: null
    };
  },

  computed: {
    // `<field>__toggle`, as the form reads it back.
    postedName() {
      const name = this.target ? document.getElementById(this.target)?.getAttribute('name') : this.fieldName;

      return name ? name.replace(/(\]?)$/, '__toggle$1') : null;
    },

    // Its key in the dynamic form's context: the field's own name.
    contextKey() {
      const name = this.fieldName ?? '';
      const own = /\[([^\]]*)\]$/.exec(name)?.[1] ?? name;

      return own ? `${own}__toggle` : null;
    },

    // Its value in the dynamic form's context, when it holds one: the
    // record's own, the form drawing one definition for many.
    contextValue() {
      const values = this.dynamicFormContext?.extensionValues;

      if (!values || !this.contextKey || !(this.contextKey in values)) {
        return null;
      }

      const value = values[this.contextKey];

      return value === true || value === 1 || value === '1' || value === 'true';
    },

    iconHtml() {
      return this.app.getServiceOrFail('icon').icon(this.icon);
    }
  },

  watch: {
    value(value) {
      if (this.contextValue === null) {
        this.on = value;
      }
    },

    // Another record, the same component: its own state.
    contextValue(value) {
      if (value !== null) {
        this.on = value;
      }
    }
  },

  // The context's, when it holds one — read once the computed are there.
  created() {
    this.on = this.contextValue ?? this.value;
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
    // The field's first strip, drawn by another of its extensions.
    join() {
      const group = this.$el?.closest?.('.form--group, .field-extended');
      const strip = group ? [...group.querySelectorAll('.field-extension--strip')].find((element) => !this.$el.contains(element)) : null;
      this.joined = strip ?? null;
    },

    toggle() {
      this.on = !this.on;

      if (this.dynamicFormContext?.extensionValues && this.contextKey) {
        this.dynamicFormContext.extensionValues[this.contextKey] = this.on;
      }

      this.$emit('update:value', this.on);
    }
  }
};
</script>
