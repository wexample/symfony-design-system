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
      submitEndpoint: 'test',
      formSubmitted: false,
    };
  },

  methods: {
    buildSubmitPayload() {
      return {
        text_simple: this.textSimple,
        text_area: this.textArea,
        behavior: this.behavior,
      };
    },

    onBeforeSubmit() {
      if (this.behavior === 'js') {
        const confirmService = this.app?.getService?.('confirm');
        if (confirmService) {
          confirmService.confirmToast({
            title: this.trans('@vue::toast.js_action.title'),
            message: this.trans('@vue::toast.js_action.message'),
            actions: [
              { key: 'r', value: 'reactivate', label: this.trans('@vue::toast.js_action.reactivate'), role: 'primary' },
              { key: 'd', value: 'dismiss', label: this.trans('@vue::toast.js_action.dismiss'), role: 'secondary' },
            ],
          }).then((response) => {
            if (response === 'reactivate') {
              this.formController.endSubmit();
            }
          });
        }
        return false;
      }
      return true;
    },

    onApiSubmitSuccess(response) {
      if (response?.type === 'redirect' && response?.url) {
        window.location.href = response.url;
        return;
      }

      if (response?.type === 'success') {
        this.formSubmitted = true;
      }
    },
  }
}

</script>
