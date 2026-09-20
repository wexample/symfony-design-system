<script>
import { loadIntoTarget } from '../../js/Helper/TargetHelper';

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-button-target',

  props: {
    app: {
      type: Object,
      required: true
    },
    href: {
      type: String,
      default: ''
    },
    // Where the page is loaded: 'modal', 'panel', or the name of an embed the
    // page holds. Empty leaves the link a link.
    target: {
      type: String,
      default: ''
    },
    targetOptions: {
      type: Object,
      default: () => ({})
    },
    icon: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: ''
    },
    className: {
      type: String,
      default: 'button'
    }
  },

  computed: {
    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    }
  },

  methods: {
    onClick(event) {
      if (!this.target || !this.href) {
        return;
      }

      // A modified click stays a navigation, so the target keeps being openable
      // as a full page in a new tab.
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
        return;
      }

      event.preventDefault();

      loadIntoTarget(this.app, this.target, this.href, { ...this.targetOptions });
    }
  }
};
</script>
