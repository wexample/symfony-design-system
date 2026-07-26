<script>

import FormVue from "../bases/form.vue";
import TextInput from "./fields/text-input.vue";
import TextareaInput from "./fields/textarea-input.vue";
import RadioInput from "./fields/radio-input.vue";
import SwitchInput from "./fields/switch-input.vue";
import SubmitButton from "./fields/submit-button.vue";

export default {
  extends: FormVue,
  components: {
    TextInput,
    TextareaInput,
    RadioInput,
    SwitchInput,
    SubmitButton
  },

  data() {
    return {
      textSimple: '',
      textArea: '',
      password: '',
      emoji: '',
      number: '',
      email: '',
      url: '',
      date: '',
      datetime: '',
      time: '',
      file: '',
      radioChoice: '',
      switchValue: false,
      behavior: 'default',
      radioChoiceOptions: [
        { value: 'option_a', label: '@vue::field.radio_choice.choice.option_a.label' },
        { value: 'option_b', label: '@vue::field.radio_choice.choice.option_b.label' },
        { value: 'option_c', label: '@vue::field.radio_choice.choice.option_c.label' },
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
        password: this.password,
        emoji: this.emoji,
        number: this.number,
        email: this.email,
        url: this.url,
        date: this.date,
        datetime: this.datetime,
        time: this.time,
        file: this.file,
        radio_choice: this.radioChoice,
        switch: this.switchValue,
        behavior: this.behavior,
      };
    },

    onBeforeSubmit() {
      if (this.behavior === 'js') {
        this.formIsSubmitting = true;
        this.formController.beginSubmit();

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
              this.formIsSubmitting = false;
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
