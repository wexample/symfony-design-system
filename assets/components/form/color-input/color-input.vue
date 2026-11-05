<script>
import FormField from '../../_abstract/form-field/form-field.vue';
import { colorNormalize, colorSwatches } from '../../../js/Helper/ColorHelper';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.color_input.';

// The twin of color-input.html.twig: a colour, its swatch opening the
// browser's picker, its hex beside it — what is held and sent, `#rrggbb` —,
// and the colours offered under it (`swatches`), one press each. What is
// typed is read as a colour once the field is left (ColorHelper); what is
// not one is kept as typed, for the form to refuse.
export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-color-input-color-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    // `#hex`, or `{ value, label }`.
    swatches: {
      type: Array,
      default: () => []
    },
    placeholder: {
      type: String,
      default: '#rrggbb'
    }
  },

  computed: {
    // What the swatch shows: the colour held, or nothing while it is not one.
    shown() {
      return colorNormalize(this.modelValue) || '';
    },

    offered() {
      return colorSwatches(this.swatches);
    },

    editable() {
      return !this.disabled && !this.readonly;
    }
  },

  methods: {
    t(key) {
      return this.trans(`${DOMAIN}${key}`);
    },

    write(value) {
      this.$emit('update:modelValue', value);
    },

    onHexInput(event) {
      this.write(event?.target?.value ?? '');
    },

    // Left, what is typed is read as a colour where it is one — what the
    // field holds now, the value given back by the parent coming after.
    onHexBlur(event) {
      const typed = event?.target?.value ?? this.modelValue;
      const normalized = colorNormalize(typed);

      if (normalized !== null && normalized !== typed) {
        this.write(normalized);
      }
    },

    onPick(event) {
      this.write(colorNormalize(event?.target?.value) ?? '');
    },

    // A colour is taken, not spelled out.
    async writeValueAssisted(value) {
      this.write(colorNormalize(value) ?? String(value ?? ''));
    }
  }
};
</script>
