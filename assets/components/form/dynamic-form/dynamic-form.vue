<script>
import Form from '../../_abstract/form/form.vue';
import TextInput from '../text-input/text-input.vue';
import TextareaInput from '../textarea-input/textarea-input.vue';
import SelectInput from '../select-input/select-input.vue';
import KeyValueInput from '../key-value-input/key-value-input.vue';
import TagsInput from '../tags-input/tags-input.vue';
import { FORM_DEFINITION_KIND_KEY_VALUE, FORM_DEFINITION_KIND_TAGS, formDefinitionFullName, formDefinitionPairs } from '../../../js/Helper/FormDefinitionHelper';
import { tagsSplit } from '../../../js/Helper/TagsHelper';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.form.';

// The field drawing each kind the norm names: a text and what the browser
// checks the shape of, several lines, a choice. A kind the norm adds and no
// field here draws is drawn as a text.
const FIELDS = {
  text: TextInput,
  email: TextInput,
  tel: TextInput,
  url: TextInput,
  password: TextInput,
  textarea: TextareaInput,
  select: SelectInput,
  key_value: KeyValueInput,
  tags: TagsInput
};

// A form of the server drawn by the design system rather than by its
// template: the form as `form_definition()` hands it — the json-schema norm's
// `form.json`, its fields each of a kind, with their label and limits —,
// each drawn by the field of its kind, sent as the drawn form would be to
// the form's processor, which validates, saves and says so. What it refuses
// is said under each field, and under the form. For a vue that holds a form
// of the server — a zone of a board, a panel of a widget — without the page
// that would draw it; the same form an agent wrote, or a page kept in a
// database, draws the same.
export default {
  extends: Form,

  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-dynamic-form-dynamic-form',

  components: {
    TextInput,
    TextareaInput,
    SelectInput,
    KeyValueInput,
    TagsInput
  },

  emits: ['saved'],

  props: {
    // What `form_definition(form)` hands over.
    form: {
      type: Object,
      required: true
    },
    // The entity sent, put where the form's address holds the placeholder
    // (`form_definition(form, { id_placeholder: '__id__' })`): one form for every
    // entity of its kind the vue shows.
    id: {
      type: String,
      default: null
    },
    // What each field starts from, by the field's name: the entity shown,
    // when the form was made for none.
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
      formDefinition: this.form,
      formDefinitionId: this.id
    };
  },

  computed: {
    submitText() {
      return this.submitLabel ?? this.trans(`${DOMAIN}save`);
    },

    // What is drawn: a hidden field is sent and never shown.
    shownFields() {
      return (this.form.fields ?? []).filter((field) => field.type !== 'hidden');
    }
  },

  watch: {
    // Another entity, or its values read again: the fields start over.
    id(id) {
      this.formDefinitionId = id;
      this.reset();
    },

    form(form) {
      this.formDefinition = form;
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
      return Object.fromEntries((this.form.fields ?? []).map((field) => {
        const given = field.name in this.values ? this.values[field.name] : undefined;

        // Pairs, not a text: the record given, or the form's own.
        if (field.type === FORM_DEFINITION_KIND_KEY_VALUE) {
          return [field.name, formDefinitionPairs(given !== undefined ? given : field.pairs)];
        }

        // Words, not a text: the list given, a text split, or the form's own.
        if (field.type === FORM_DEFINITION_KIND_TAGS) {
          const tags = given !== undefined ? given : field.tags;

          return [field.name, Array.isArray(tags) ? [...tags] : tagsSplit(String(tags ?? ''), field.separator ?? ',')];
        }

        return [field.name, String((given !== undefined ? given : field.value) ?? '')];
      }));
    },

    reset() {
      this.draft = this.startDraft();
      this.clearFormErrors();
    },

    fieldComponent(field) {
      return FIELDS[field.type] ?? TextInput;
    },

    // The field's name as it is sent, under the form's: the name it is
    // registered under, which the answer's errors come back by.
    fullName(field) {
      return formDefinitionFullName(this.form, field.name);
    },

    // The browser's own kind for a text: what it checks the shape of.
    inputType(field) {
      return ['email', 'tel', 'url', 'password'].includes(field.type) ? field.type : 'text';
    },

    // A choice's options as the select takes them, a group's laid flat.
    optionsOf(field) {
      const flatten = (choices = []) => choices.flatMap((choice) => (choice.options ? flatten(choice.options) : [{ value: choice.value ?? '', label: choice.label }]));

      return flatten(field.choices);
    },

    // The endpoint is the form's own address: named here for the mixin's
    // guard, used by none of its transports.
    getSubmitEndpoint() {
      return this.form.action;
    },

    buildSubmitPayload() {
      return { ...this.draft };
    },

    onBeforeSubmit() {
      return !this.readonly;
    },

    onApiSubmitSuccess(response) {
      Form.methods.onApiSubmitSuccess.call(this, response);
      this.$emit('saved', { values: JSON.parse(JSON.stringify(this.draft)), id: this.id, payload: response });
    }
  }
};
</script>
