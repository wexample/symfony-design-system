<script>
import { FORM_DEFINITION_ID_PLACEHOLDER } from '../../../../js/Helper/FormDefinitionHelper';
import { loadIntoTarget } from '../../../../js/Helper/TargetHelper';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.field_open.';

// Where the value goes in the address.
export const FIELD_OPEN_VALUE_PLACEHOLDER = '__value__';

export const FIELD_OPEN_TARGET_TAB = 'tab';

// The `open` extension of a field (FieldExtensionsHelper): what the value
// designates — a record, a page — opened from the field, at `url`, the value
// where it says `__value__`, the record's id where it says `__id__`, in a new
// tab, a modal or a panel (`in` — `target` is what every extension is given
// of the page's field). A button at the end of the field's
// frame, glued to it, pressed only while the field holds a value. The field
// is read as the vue field's value (`modelValue`), or — drawn by the twig
// theme beside the field (`target`, its control's id) — as the value of the
// named control of its group, followed as it changes; there the button steps
// to the end of the field's frame itself.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-extension-open-open',

  inject: {
    dynamicFormContext: { default: null }
  },

  props: {
    url: {
      type: String,
      required: true
    },
    // Where it opens: `tab`, `modal` or `panel`.
    in: {
      type: String,
      default: FIELD_OPEN_TARGET_TAB
    },
    label: {
      type: String,
      default: null
    },
    modelValue: {
      type: [String, Number],
      default: null
    },
    // The id of the field's control, when the field is the page's.
    target: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      // The page's field value, followed (twig theme).
      held: ''
    };
  },

  computed: {
    value() {
      const value = this.target ? this.held : this.modelValue;

      return value === null || value === undefined ? '' : String(value);
    },

    // The address, the value and the record put in; none while either is missing.
    href() {
      if (!this.value) {
        return null;
      }

      let href = this.url.replaceAll(FIELD_OPEN_VALUE_PLACEHOLDER, encodeURIComponent(this.value));

      if (href.includes(FORM_DEFINITION_ID_PLACEHOLDER)) {
        const id = this.dynamicFormContext?.id ?? null;

        if (!id) {
          return null;
        }

        href = href.replaceAll(FORM_DEFINITION_ID_PLACEHOLDER, id);
      }

      return href;
    },

    resolvedLabel() {
      return this.label || this.trans(`${DOMAIN}label`);
    }
  },

  mounted() {
    if (!this.target) {
      return;
    }

    const group = this.targetGroup();

    this.placeBesideTarget();
    this.held = this.readTarget();
    group?.addEventListener('input', this.onTargetChange);
    group?.addEventListener('change', this.onTargetChange);
    group?.addEventListener('select:change', this.onTargetChange);
  },

  beforeUnmount() {
    const group = this.targetGroup();

    group?.removeEventListener('input', this.onTargetChange);
    group?.removeEventListener('change', this.onTargetChange);
    group?.removeEventListener('select:change', this.onTargetChange);
  },

  methods: {
    t(key) {
      return this.trans(`${DOMAIN}${key}`);
    },

    targetGroup() {
      return this.target ? document.getElementById(this.target)?.closest('.form--group') ?? null : null;
    },

    // The value the group posts: its first named control.
    readTarget() {
      const control = this.targetGroup()?.querySelector('input[name]:not([type="search"]), select[name], textarea[name]');

      return control?.value ?? '';
    },

    onTargetChange() {
      this.held = this.readTarget();
    },

    // Drawn after the field by the twig theme: moved to the end of the
    // field's frame, the two read as one, as the vue field holds it.
    placeBesideTarget() {
      const control = document.getElementById(this.target);
      const frame = control?.closest('.form--input-group, .select, .code-input') ?? control;
      const host = this.$el?.parentElement;

      if (!frame || !host || frame.parentElement?.classList.contains('form--control--ended')) {
        return;
      }

      const row = document.createElement('div');
      row.className = 'form--control form--control--ended';
      frame.replaceWith(row);
      row.append(frame, host);
    },

    open() {
      if (!this.href) {
        return;
      }

      if (this.in === FIELD_OPEN_TARGET_TAB) {
        window.open(this.href, '_blank', 'noopener');

        return;
      }

      loadIntoTarget(this.app, this.in, this.href);
    }
  }
};
</script>
