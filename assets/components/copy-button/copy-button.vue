<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';
import { clipboardCopy } from '../../js/Helper/ClipboardHelper';

// How long the check — or the cross — stays once the button was used.
const COPY_BUTTON_FEEDBACK = 2000;

// The twin of components/copy-button/copy-button.html.twig: same markup, same
// states. The text is the one given, read when clicked.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-copy-button-copy-button',

  props: {
    value: {
      type: String,
      default: ''
    },
    // Says « Copy » beside the glyph.
    label: {
      type: Boolean,
      default: false
    },
    // Says it copied in a toast too: where the button is small, beside what
    // it copies, and its check is easily missed.
    toast: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      // null until used, then whether the clipboard took it.
      copied: null
    };
  },

  computed: {
    iconsHtml() {
      const icons = this.app.getServiceOrFail(IconService);

      return {
        copy: icons.icon('ph:bold/copy'),
        done: icons.icon('ph:bold/check'),
        failed: icons.icon('ph:bold/x')
      };
    },

    copyLabel() {
      return this.trans('@vue::copy.copy');
    },

    // What happened, said aloud and under the pointer.
    status() {
      if (this.copied === null) {
        return '';
      }

      return this.trans(this.copied ? '@vue::copy.copied' : '@vue::copy.failed');
    }
  },

  beforeUnmount() {
    window.clearTimeout(this.timer);
  },

  methods: {
    async onClick() {
      this.copied = await clipboardCopy(this.value);

      if (this.toast) {
        this.app.services.toast?.show({ type: this.copied ? 'success' : 'error', message: this.status });
      }

      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => {
        this.copied = null;
      }, COPY_BUTTON_FEEDBACK);
    }
  }
};
</script>
