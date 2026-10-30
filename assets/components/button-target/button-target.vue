<script>
import { loadIntoTarget, targetEmbeddedOf } from '../../js/Helper/TargetHelper';
import { rippleAgentPress } from '../../js/Helper/RippleHelper';

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-button-target-button-target',

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
    // Where it opens when its target is not on the page — an embed a page
    // opened in a panel does not hold: `panel` unless said, or what an element
    // around it says (`data-target-embedded`).
    targetEmbedded: {
      type: String,
      default: null
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
    // What an agent reads and does (`button-target.ai.yml`, shared with the
    // twig twin).
    getAgentLabel() {
      return this.label || this.$el?.getAttribute('aria-label') || this.$el?.getAttribute('title') || undefined;
    },

    getAgentState() {
      return { opens: this.target || null };
    },

    getAgentActions() {
      return {
        click: async () => {
          await rippleAgentPress(this.app, this.$el);
          this.$el.click();

          return { clicked: this.getAgentLabel(), opened: this.target || null };
        }
      };
    },

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

      loadIntoTarget(this.app, this.target, this.href, {
        ...this.targetOptions,
        targetEmbedded: targetEmbeddedOf(this.$el, this.targetEmbedded),
      });
    }
  }
};
</script>
