<script>
// The twin of file-path.html.twig.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-file-path-file-path',

  props: {
    path: {
      type: String,
      default: ''
    }
  },

  computed: {
    parts() {
      const cut = (this.path || '').split('/');
      const name = cut.pop();

      return {
        folder: cut.length ? `${cut.join('/')}/` : '',
        name
      };
    }
  }
};
</script>
