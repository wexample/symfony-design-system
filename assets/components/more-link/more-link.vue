<script>
// The twin of more-link.html.twig: the way on from a short list.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-more-link-more-link',

  props: {
    href: {
      type: String,
      required: true
    },
    label: {
      type: String,
      default: ''
    }
  }
};
</script>
