<script>

import FormVue from "../bases/form.vue";

export default {
  extends: FormVue,

  data() {
    return {
      name: '',
      submitEndpoint: 'test/simple-error',
    };
  },

  methods: {
    buildSubmitPayload() {
      return {
        text: this.name,
      };
    }
  }
}

</script>
