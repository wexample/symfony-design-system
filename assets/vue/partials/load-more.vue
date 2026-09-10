<script>
import Dots from './dots.vue';

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-load-more',

  components: {
    Dots
  },

  emits: ['load'],

  props: {
    label: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: null
    },
    isLoading: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  }
};
</script>
