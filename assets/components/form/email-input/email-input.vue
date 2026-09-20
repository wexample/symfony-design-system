<script>
import TextInput from '../text-input/text-input.vue';

export default {
  extends: TextInput,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-email-input',
  props: {
    type: { default: 'email' }
  }
};
</script>
