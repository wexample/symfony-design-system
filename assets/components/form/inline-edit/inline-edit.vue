<script>
import AdaptiveService from '@wexample/symfony-loader/js/Services/AdaptiveService';
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';

// Where its own words are.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.inline_edit.';

// What `form_props()` puts where the entity's id stands in the address.
const ID_PLACEHOLDER = '__id__';

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

    // The fields as the drawn form would send them: each with its value,
    // the edited one with what was typed, and the token.
    buildFormData() {
      const data = new FormData();

      Object.entries(this.form.fields ?? {}).forEach(([name, field]) => {
        const value = name === this.field ? this.draft.trim() : field.value;

        if (value !== null && value !== undefined && typeof value !== 'object') {
          data.append(field.name, String(value));
        }
      });

      if (this.form.token) {
        data.append(this.form.token.name, this.form.token.value);
      }

      return data;
    },

    buildAction() {
      return this.id ? this.form.action.replace(ID_PLACEHOLDER, this.id) : this.form.action;
    },

    async save() {
      if (!this.canSave) {
        return;
      }

      this.isSaving = true;
      this.errors = [];

      try {
        const payload = await this.app.getServiceOrFail(AdaptiveService).requestData(this.buildAction(), {
          method: this.form.method ?? 'POST',
          body: this.buildFormData()
        });

        if (!payload || payload.ok === false) {
          this.errors = this.readErrors(payload);

          return;
        }

        this.notify(payload);
        const value = this.draft.trim();
        this.savedValue = value;
        this.editing = false;
        this.$emit('update:editing', false);
        this.$emit('saved', { value, id: this.id, payload });
      } finally {
        this.isSaving = false;
      }
    },

    // What the processor refused, translated as a drawn form's errors are:
    // the field's own, the form's, or that it could not be sent.
    readErrors(payload) {
      const errors = payload?.form?.errors;
      const messages = [
        ...(errors?.fields?.[this.fieldProps?.name] ?? []),
        ...(errors?.form ?? [])
      ];
      const locale = this.app.getServiceOrFail(LocaleService);

      return messages.length
        ? messages.map((message) => locale.trans(message, {}, payload.translations ?? {}))
        : [this.trans('WexampleSymfonyDesignSystemBundle.common.system::frontend.inline_edit.failed')];
    },

    notify(payload) {
      const notification = payload?.notification;

      if (notification) {
        this.app.services.toast?.show({
          type: notification.type,
          message: this.app.getServiceOrFail(LocaleService).trans(notification.message, {}, payload.translations ?? {})
        });
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
