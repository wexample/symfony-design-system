<script>
import { fieldExpressionMatches } from '../../../../js/Helper/FieldExpressionHelper';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.field_expression.';

// The `expression` extension of a field (FieldExtensionsHelper): an
// expression in place of the value — a template computing it on each run.
// A strip under the field switches it to an editor of `language` and back;
// it starts as an expression when the value matches `pattern`. While on, the
// field's control steps aside (the base is told, `control`) and the editor
// holds the value — the editor the form hands over for code when it has one
// (`fieldComponents.code`), a plain text area otherwise. The value kept
// before switching is given back when switching back. The field is reached
// as the vue field's value (`modelValue`) or as the page's element (`target`,
// the twig theme): there, the control is disabled while the editor, named as
// it, is what the form posts.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-extension-expression-expression',

  inject: {
    dynamicFormContext: { default: null }
  },

  emits: ['update:modelValue', 'control'],

  props: {
    language: {
      type: String,
      default: null
    },
    pattern: {
      type: String,
      default: null
    },
    rows: {
      type: Number,
      default: 2
    },
    modelValue: {
      type: [String, Number, Boolean],
      default: null
    },
    target: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      on: false,
      // The value held before the expression, given back once it is left.
      kept: null,
      // What the editor holds while on; the page's field value otherwise.
      held: ''
    };
  },

  computed: {
    // The editor the form hands over for code, when it has one.
    editor() {
      return this.dynamicFormContext?.components?.code?.component ?? null;
    },

    targetName() {
      return this.targetElement()?.getAttribute('name') ?? null;
    }
  },

  watch: {
    modelValue(value) {
      if (!this.target && !this.on) {
        this.held = String(value ?? '');
      }
    }
  },

  mounted() {
    this.held = this.read();

    if (fieldExpressionMatches(this.pattern, this.held)) {
      this.switchOn(false);
    }
  },

  beforeUnmount() {
    this.stepAside(false);
  },

  methods: {
    t(key) {
      return this.trans(`${DOMAIN}${key}`);
    },

    targetElement() {
      return this.target ? document.getElementById(this.target) : null;
    },

    read() {
      return this.target ? (this.targetElement()?.value ?? '') : String(this.modelValue ?? '');
    },

    write(content) {
      this.held = content;

      if (!this.target) {
        this.$emit('update:modelValue', content);

        return;
      }

      const element = this.targetElement();

      if (element) {
        element.value = content;
        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
      }
    },

    // The page's control stepping aside: disabled, so the editor named as
    // it is what the form posts; and hidden with what frames it.
    stepAside(aside) {
      const element = this.targetElement();

      if (!element) {
        return;
      }

      const frame = element.closest('.select, .switch, .form--input-group, .code-input') ?? element;
      frame.hidden = aside;
      element.disabled = aside;
      frame.querySelectorAll('input, select, textarea').forEach((control) => { control.disabled = aside; });
    },

    switchOn(keep = true) {
      if (keep) {
        this.kept = this.read();
      }

      this.on = true;
      this.held = this.read();
      this.stepAside(true);
      this.$emit('control', true);
    },

    switchOff() {
      this.on = false;
      this.stepAside(false);
      this.$emit('control', false);
      this.write(this.kept ?? '');
      this.kept = null;
    },

    toggle() {
      this.on ? this.switchOff() : this.switchOn();
    },

    onEditorInput(value) {
      const content = typeof value === 'string' ? value : (value?.target?.value ?? '');
      this.held = content;

      if (!this.target) {
        this.$emit('update:modelValue', content);
      }
    }
  }
};
</script>
