<script>

import FormVue from "../bases/form.vue";
import TextInput from "./fields/text-input.vue";
import SubmitButton from "./fields/submit-button.vue";

export default {
  extends: FormVue,
  components: {
    TextInput,
    SubmitButton
  },

  data() {
    return {
      name: '',
      submitEndpoint: 'test/simple-error',
    };
  },

  methods: {
    buildSubmitPayload() {
      return {
        text_simple: this.name,
      };
    }
  }
}

</script>
