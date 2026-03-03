<script>

import FormVue from "../bases/form.vue";

export default {
  extends: FormVue,

  data() {
    return {
      name: ''
    };
  },

  methods: {
    onSubmit() {
      console.log('SUBMIT!');
    }
  }
}

</script>
