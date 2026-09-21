<script>

import Spinner from '../spinner/spinner.vue';

// The twin of button.html.twig: the same three spans, the same classes. What the
// twig reads off its options, this reads off props; the click it cannot handle
// itself is emitted, since inside a vue tree the parent is what knows.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-button-button',

  components: {
    Spinner
  },

  emits: ['click'],

  props: {
    label: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: null
    },
    type: {
      type: String,
      default: 'button'
    },
    disabled: {
      type: Boolean,
      default: false
    },
    isLoading: {
      type: Boolean,
      default: false
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  }
};
</script>
