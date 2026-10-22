<script>
// The twin of text-link.html.twig.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-text-link-text-link',

  props: {
    href: {
      type: String,
      required: true
    },
    label: {
      type: String,
      default: ''
    },
    external: {
      type: Boolean,
      default: false
    }
  }
};
</script>
