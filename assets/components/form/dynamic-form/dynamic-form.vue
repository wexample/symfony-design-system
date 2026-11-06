<script>
import { markRaw } from 'vue';
import Form from '../../_abstract/form/form.vue';
import TextInput from '../text-input/text-input.vue';
import TextareaInput from '../textarea-input/textarea-input.vue';
import SelectInput from '../select-input/select-input.vue';
import KeyValueInput from '../key-value-input/key-value-input.vue';
import TagsInput from '../tags-input/tags-input.vue';
import SearchSelectInput from '../search-select-input/search-select-input.vue';
import ColorInput from '../color-input/color-input.vue';
import SwitchInput from '../switch-input/switch-input.vue';
import CheckboxInput from '../checkbox-input/checkbox-input.vue';
import NumberInput from '../number-input/number-input.vue';
import { FORM_DEFINITION_KIND_KEY_VALUE, FORM_DEFINITION_KIND_TAGS, FORM_DEFINITION_KINDS_TOGGLE, formDefinitionChecked, formDefinitionFullName, formDefinitionPairs } from '../../../js/Helper/FormDefinitionHelper';
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
  tags: TagsInput,
  search_select: SearchSelectInput,
  color: ColorInput,
  switch: SwitchInput,
  checkbox: CheckboxInput,
  number: NumberInput
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
    TagsInput,
    SearchSelectInput,
    ColorInput,
    SwitchInput,
    CheckboxInput,
    NumberInput
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
    },
    // The fields of kinds a package of its own draws — code, from
    // symfony-coding —, by kind: `{ code: { component, props(field, draft) } }`.
    // The design system cannot depend on what depends on it: the caller hands
    // the field over, and the form gives it what any field is given — its
    // value, name, label, errors —, plus what `props` reads of the definition.
    fieldComponents: {
      type: Object,
      default: () => ({})
    }
  },

  data() {
    return {
      draft: this.startDraft(),
      formDefinition: this.form,
      formDefinitionId: this.id,
      // What the fields' extensions may ask of the form holding them: the
      // record sent, how many times the form was saved, the components it
      // was handed for the kinds it does not draw itself.
      dynamicFormContext: {
        id: this.id,
        saved: 0,
        // Components, not state: left as they are, or vue would watch them.
        components: markRaw({ ...this.fieldComponents })
      }
    };
  },

  provide() {
    return {
      dynamicFormContext: this.dynamicFormContext
    };
  },

  computed: {
    submitText() {
      return this.submitLabel ?? this.trans(`${DOMAIN}save`);
    },

    // Fields side by side, as many as the form says (`columns`) and the room
    // holds, the layout of the design system's forms (form--columns).
    layoutClass() {
      const columns = Number(this.form.columns ?? 1);

      return {
        'form--columns': columns > 1,
        'form--columns-2': columns === 2
      };
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
      this.dynamicFormContext.id = id;
      this.reset();
    },

    fieldComponents(components) {
      this.dynamicFormContext.components = markRaw({ ...components });
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

        // Yes or no, not a text: what is given, or whether the form has it on.
        if (FORM_DEFINITION_KINDS_TOGGLE.includes(field.type)) {
          return [field.name, formDefinitionChecked(given !== undefined ? given : field.checked)];
        }

        return [field.name, String((given !== undefined ? given : field.value) ?? '')];
      }));
    },

    reset() {
      this.draft = this.startDraft();
      this.clearFormErrors();
    },

    // A field of a kind handed over by the caller, when it is one.
    customField(field) {
      const entry = this.fieldComponents?.[field.type];

      return entry?.component ? entry : null;
    },

    customProps(field) {
      return this.customField(field)?.props?.(field, this.draft) ?? {};
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
      // Saved: the fields' extensions may read again — the versions, one more.
      this.dynamicFormContext.saved += 1;
      this.$emit('saved', { values: JSON.parse(JSON.stringify(this.draft)), id: this.id, payload: response });
    }
  }
};
</script>
