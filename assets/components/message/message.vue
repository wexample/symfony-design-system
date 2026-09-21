<script>

// The twin of message.html.twig. The four flavours carry the icon the twig
// functions of MessageExtension put in for the caller; naming them here keeps
// a vue message looking like a server-rendered one without being told.
const DEFAULT_ICONS = {
  info: 'ph:bold/info',
  success: 'ph:bold/check-circle',
  warning: 'ph:bold/warning',
  error: 'ph:bold/x-circle'
};

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-message-message',

  props: {
    type: {
      type: String,
      default: 'info'
    },
    // Already translated, as every other vue component of the system takes
    // its label: the twig side runs `|trans` because it is the caller's last
    // chance, here the caller is still a template and has had its own.
    title: {
      type: String,
      default: null
    },
    body: {
      type: String,
      default: null
    },
    // Explicitly false to state a message that carries no icon at all.
    icon: {
      type: [String, Boolean],
      default: null
    },
    compact: {
      type: Boolean,
      default: false
    },
    extraClass: {
      type: String,
      default: null
    }
  },

  computed: {
    messageClass() {
      const classes = ['message', `message--${this.type}`];

      if (this.compact) {
        classes.push('message--compact');
      }

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    },

    iconName() {
      if (this.icon === false) {
        return null;
      }

      return this.icon || DEFAULT_ICONS[this.type] || null;
    },

    iconHtml() {
      return this.iconName ? this.app.getServiceOrFail('icon').icon(this.iconName) : '';
    }
  }
};
</script>
