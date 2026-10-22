<script>

// The twin of menu-separator.html.twig: the line that names a run of menu rows.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-menu-separator-menu-separator',

  props: {
    // Already translated, as every other vue component of the system takes it.
    label: {
      type: String,
      default: ''
    },
    extraClass: {
      type: String,
      default: null
    }
  }
};
</script>
