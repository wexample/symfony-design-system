<script>
import { submitFormProps } from '../../../js/Helper/FormPropsHelper';

// Where its own words are.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.inline_edit.';

// One field of a form of the application edited in place: what it shows —
// its default slot, a select, a value — and a pencil in the frame; pressed,
// the frame holds the field instead, its value written in, saved by Enter or
// ✓, let go by Escape or ✕. Sent as the drawn form would be — the fields of
// `form` (`form_props()`), the one edited holding what was typed — to the
// form's processor, which validates, saves and says so; what it refuses is
// said under the frame, the field kept open.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-inline-edit-inline-edit',

  emits: ['saved', 'update:editing'],

  props: {
    // What `form_props(form)` hands over. None: the frame and what it shows,
    // no pencil — a reader who may not change the value.
    form: {
      type: Object,
      default: null
    },
    // The field edited, by its name in the form.
    field: {
      type: String,
      required: true
    },
    // The entity sent, put where the form's address holds the placeholder
    // (`form_props(form, { id_placeholder: '__id__' })`).
    id: {
      type: String,
      default: null
    },
    // What the field starts from: the value shown — the step chosen in a
    // select —, the form's own value else.
    value: {
      type: String,
      default: null
    },
    // What names the field, for whoever cannot see what it edits.
    label: {
      type: String,
      default: null
    },
    // The pencil in the frame; off, the editing is opened from outside
    // (`edit()`).
    pencil: {
      type: Boolean,
      default: true
    },
    // Read as the text it is — a title in a header —, no frame round it, the
    // pencil shown under the pointer; the frame only while it is edited.
    bare: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      editing: false,
      draft: '',
      isSaving: false,
      errors: [],
      // What was saved last, shown when the page gives no value of its own.
      savedValue: null
    };
  },

  computed: {
    fieldProps() {
      return this.form?.fields?.[this.field] ?? null;
    },

    startValue() {
      return this.value ?? this.savedValue ?? this.fieldProps?.value ?? '';
    },

    editLabel() {
      return this.label ? `${this.trans(`${DOMAIN}edit`)} · ${this.label}` : this.trans(`${DOMAIN}edit`);
    },

    saveLabel() {
      return this.trans(`${DOMAIN}save`);
    },

    cancelLabel() {
      return this.trans(`${DOMAIN}cancel`);
    },

    canSave() {
      return !this.isSaving && (!this.fieldProps?.required || this.draft.trim() !== '');
    },

    iconHtml() {
      return {
        edit: this.renderIcon('ph:bold/pencil-simple'),
        save: this.renderIcon('ph:bold/check'),
        cancel: this.renderIcon('ph:bold/x')
      };
    }
  },

  methods: {
    edit() {
      this.draft = String(this.startValue ?? '');
      this.errors = [];
      this.editing = true;
      this.$emit('update:editing', true);
      this.$nextTick(() => {
        this.$refs.input?.focus();
        this.$refs.input?.select();
      });
    },

    cancel() {
      this.editing = false;
      this.errors = [];
      this.$emit('update:editing', false);
    },

    async save() {
      if (!this.canSave) {
        return;
      }

      this.isSaving = true;
      this.errors = [];

      try {
        const answer = await submitFormProps(this.app, this.form, {
          id: this.id,
          values: { [this.field]: this.draft.trim() },
          failedMessage: this.trans(`${DOMAIN}failed`)
        });

        if (!answer.ok) {
          this.errors = [...(answer.errors.fields[this.field] ?? []), ...answer.errors.form];

          return;
        }

        const value = this.draft.trim();
        this.savedValue = value;
        this.editing = false;
        this.$emit('update:editing', false);
        this.$emit('saved', { value, id: this.id, payload: answer.payload });
      } finally {
        this.isSaving = false;
      }
    },

    onKeydown(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        this.save();
      } else if (event.key === 'Escape') {
        event.preventDefault();
        event.stopPropagation();
        this.cancel();
      }
    },

    renderIcon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    }
  }
};
</script>
