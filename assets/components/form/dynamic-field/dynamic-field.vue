<script>
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
import CollectionInput from '../collection-input/collection-input.vue';
import { formDefinitionCollectionTemplate } from '../../../js/Helper/FormDefinitionHelper';

// One field of a form's definition (`field.schema.json`), drawn by the field
// of its kind: a text and what the browser checks the shape of, several
// lines, a choice, words, pairs, a yes or no, a number, a collection — whose
// items are each this same field again, for its entry. A kind the norm adds
// and no field here draws is drawn as a text; a kind the form was handed
// (`dynamicFormContext.components`, code from symfony-coding) by its own.
export default {
  // Named, for a collection's items to draw themselves with it.
  name: 'DynamicField',

  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-dynamic-field-dynamic-field',

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
    NumberInput,
    CollectionInput
  },

  inject: {
    dynamicFormContext: {
      default: null
    }
  },

  emits: ['update:modelValue'],

  props: {
    field: {
      type: Object,
      required: true
    },
    modelValue: {
      type: null,
      default: null
    },
    // The name it is sent under, which its errors come back by.
    name: {
      type: String,
      default: null
    },
    // The errors said of a name, the form's.
    errorsOf: {
      type: Function,
      default: () => []
    },
    readonly: {
      type: Boolean,
      default: false
    },
    // The form's whole draft, for a handed kind reading another field.
    draft: {
      type: Object,
      default: () => ({})
    },
    // An item of a collection: no label, no help — the collection says them.
    bare: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    value: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      }
    },

    collectionTemplate() {
      return formDefinitionCollectionTemplate(this.field);
    }
  },

  methods: {
    // A field of a kind handed over by the caller, when it is one.
    customField(field) {
      const entry = this.dynamicFormContext?.components?.[field.type];

      return entry?.component ? entry : null;
    },

    customProps(field) {
      return this.customField(field)?.props?.(field, this.draft) ?? {};
    },

    // The browser's own kind for a text: what it checks the shape of.
    inputType(field) {
      return ['email', 'tel', 'url', 'password'].includes(field.type) ? field.type : 'text';
    },

    // A choice's options as the select takes them, a group's laid flat.
    optionsOf(field) {
      const flatten = (choices = []) => choices.flatMap((choice) => (choice.options ? flatten(choice.options) : [{ value: choice.value ?? '', label: choice.label }]));

      return flatten(field.choices);
    }
  }
};
</script>
