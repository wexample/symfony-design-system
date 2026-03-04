<script>

import FormVue from "../bases/form.vue";
import TextInput from "./fields/text-input.vue";
import TextareaInput from "./fields/textarea-input.vue";
import SelectInput from "./fields/select-input.vue";
import SubmitButton from "./fields/submit-button.vue";

export default {
  extends: FormVue,
  components: {
    TextInput,
    TextareaInput,
    SelectInput,
    SubmitButton
  },

  data() {
    return {
      textSimple: '',
      textArea: '',
      behavior: 'default',
      behaviorOptions: [
        { value: 'default', label: '@vue::field.behavior.choice.default.label' },
        { value: 'js', label: '@vue::field.behavior.choice.js.label' },
        { value: 'error', label: '@vue::field.behavior.choice.error.label' },
        { value: 'redirect', label: '@vue::field.behavior.choice.redirect.label' }
      ],
      submitEndpoint: 'test/simple-error',
    };
  },

  methods: {
    buildSubmitPayload() {
      return {
        text_simple: this.textSimple,
        text_area: this.textArea,
        behavior: this.behavior,
      };
    }
  }
}

</script>
