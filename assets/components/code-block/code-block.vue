<script>

// The twin of code-block.html.twig. The twig runs the code through `|e`; here
// the interpolation escapes it, which is the same promise kept differently.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-code-block-code-block',

  props: {
    code: {
      type: String,
      required: true
    },
    lang: {
      type: String,
      default: 'text'
    }
  }
};
</script>
