<script>

const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.document.';

// The twin of document-embed.html.twig. The classes and the addresses the twig
// side builds in DocumentExtension are built here from the same options, so a
// frame written by vue sits in its parent exactly as a rendered one does.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-document-embed-document-embed',

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
    // The ways out of the frame: true for the document's own address, or
    // another one — a route serving it as an attachment.
    open: {
      type: [Boolean, String],
      default: false
    },
    download: {
      type: [Boolean, String],
      default: false
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
    },

    openHref() {
      return this.resolveHref(this.open);
    },

    downloadHref() {
      return this.resolveHref(this.download);
    },

    openLabel() {
      return this.trans(`${T}open`);
    },

    downloadLabel() {
      return this.trans(`${T}download`);
    },

    openIconHtml() {
      return this.app.getServiceOrFail('icon').icon('ph:bold/arrow-square-out');
    },

    downloadIconHtml() {
      return this.app.getServiceOrFail('icon').icon('ph:bold/download-simple');
    }
  },

  methods: {
    resolveHref(option) {
      if (option === true) {
        return this.src;
      }

      return option || null;
    }
  }
};
</script>
