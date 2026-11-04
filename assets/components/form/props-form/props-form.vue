<script>
import TextInput from '../text-input/text-input.vue';
import TextareaInput from '../textarea-input/textarea-input.vue';
import { submitFormProps } from '../../../js/Helper/FormPropsHelper';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.form.';

// A form of the application drawn by the design system rather than by its
// template: its fields as `form_props()` hands them — their label, their
// kind, their limits —, each drawn by the field of its kind, sent as the
// drawn form would be to the form's processor, which validates, saves and
// says so. What it refuses is said under each field, and under the form.
// For a vue that holds a form of the server — a zone of a board, a panel of
// a widget — without the page that would draw it.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-props-form-props-form',

  components: {
    TextInput,
    TextareaInput
  },

  emits: ['saved'],

  props: {
    // What `form_props(form)` hands over.
    form: {
      type: Object,
      required: true
    },
    // The entity sent, put where the form's address holds the placeholder
    // (`form_props(form, { id_placeholder: '__id__' })`): one form for every
    // entity of its kind the vue shows.
    id: {
      type: String,
      default: null
    },
    // What each field starts from, by its key: the entity shown, when the
    // form was made for none.
    values: {
      type: Object,
      default: () => ({})
    },
    // Read, not changed: the fields settled, no button.
    readonly: {
      type: Boolean,
      default: false
    },
    submitLabel: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      draft: this.startDraft(),
      errors: { fields: {}, form: [] },
      isSaving: false
    };
  },

  computed: {
    submitText() {
      return this.submitLabel ?? this.trans(`${DOMAIN}save`);
    }
  },

  watch: {
    // Another entity, or its values read again: the fields start over.
    id() {
      this.reset();
    },

    values: {
      handler() {
        this.reset();
      },
      deep: true
    }
  },

  methods: {
    startDraft() {
      return Object.fromEntries(Object.entries(this.form.fields ?? {}).map(([key, field]) => [
        key,
        String((key in this.values ? this.values[key] : field.value) ?? '')
      ]));
    },

    reset() {
      this.draft = this.startDraft();
      this.errors = { fields: {}, form: [] };
    },

    async submit() {
      if (this.readonly || this.isSaving) {
        return;
      }

      this.isSaving = true;
      this.errors = { fields: {}, form: [] };

      try {
        const answer = await submitFormProps(this.app, this.form, {
          id: this.id,
          values: this.draft,
          failedMessage: this.trans(`${DOMAIN}failed`)
        });

        if (!answer.ok) {
          this.errors = answer.errors;

          return;
        }

        this.$emit('saved', { values: { ...this.draft }, id: this.id, payload: answer.payload });
      } finally {
        this.isSaving = false;
      }
    }
  }
};
</script>
