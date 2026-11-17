<script>
// The actions of what a zone holds, sent to the head of the zone it stands in
// — a panel of the tool dock (`data-zone-head-actions`, components/tool-dock)
// —: adding one to a list, switching a chat's sound. Out of such a zone — the
// same list on a page of its own —, drawn where it is written, in a bar of its
// own (`fallbackClass`). A component holding them draws them once, wherever it
// is shown.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-head-actions-zone-head-actions',

  props: {
    // The bar they stand in when no head takes them.
    fallbackClass: {
      type: String,
      default: 'toolbar toolbar--compact'
    }
  },

  data() {
    return {
      // Looked for once in the page: nothing drawn before, so nothing jumps.
      resolved: false,
      target: null
    };
  },

  mounted() {
    // The nearest head: a zone nested in the panel — a page opened in it —
    // sends its actions to the panel's, the one zone that has a head.
    const frame = this.$el.closest('.tool-dock--frame');

    this.target = frame?.querySelector(':scope > .zone--head [data-zone-head-actions]') ?? null;
    this.resolved = true;
  }
};
</script>
