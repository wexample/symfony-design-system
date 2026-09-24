<script>
// The twin of timeline.html.twig. What the twig takes as `body` markup is a
// scoped slot here, `#item="{ item, index }"`, so anything — another
// component, a form — can stand under an item's title.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-timeline-timeline',

  props: {
    // Each item is { title, meta, text, icon, state, tone }, all optional.
    items: {
      type: Array,
      required: true
    },
    numbered: {
      type: Boolean,
      default: false
    }
  },

  methods: {
    itemClasses(item) {
      return [
        'timeline--item',
        item.state ? `is-${item.state}` : null,
        item.tone ? `timeline--item--${item.tone}` : null
      ];
    },

    iconHtml(icon) {
      return this.app.getServiceOrFail('icon').icon(icon);
    }
  }
};
</script>
