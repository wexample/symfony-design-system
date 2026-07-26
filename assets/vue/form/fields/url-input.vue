<script>
import TextInput from './text-input.vue';

export default {
  extends: TextInput,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-url-input',
  props: {
    type: { default: 'url' }
  }
};
</script>
