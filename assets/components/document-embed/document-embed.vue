<script>
import ButtonTarget from '../button-target/button-target.vue';

// The twin of document-embed.html.twig. The classes the twig side builds in
// DocumentExtension are built here from the same options, so a frame written
// by vue sits in its parent exactly as a rendered one does.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-document-embed-document-embed',

  components: {
    ButtonTarget,
  },

  props: {
    src: {
      type: String,
      required: true
    },
    title: {
      type: String,
      default: ''
    },
    // '16-9', '4-3'… Without one the box has no height of its own, so it takes
    // the one its parent gives.
    ratio: {
      type: String,
      default: null
    },
    loading: {
      type: String,
      default: 'lazy'
    },
    extraClass: {
      type: String,
      default: null
    },
    // What the application does with the document, none given by default:
    // [{ icon, label, href, target?, download?, new_window? }], the labels
    // already translated.
    actions: {
      type: Array,
      default: () => []
    }
  },

  computed: {
    wrapperClass() {
      const classes = ['document-embed'];

      if (!this.ratio) {
        classes.push('document-embed--fill');
      }

      if (this.extraClass) {
        classes.push(this.extraClass);
      }

      return classes.join(' ');
    },

    frameClass() {
      return `media ${this.ratio ? `media--${this.ratio}` : 'media--fill'}`;
    }
  },

  methods: {
    renderIcon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    }
  }
};
</script>
