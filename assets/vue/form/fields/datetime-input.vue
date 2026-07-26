<script>
import TextInput from './text-input.vue';

export default {
  extends: TextInput,
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-form-fields-datetime-input',
  props: {
    type: { default: 'datetime-local' }
  }
};
</script>
